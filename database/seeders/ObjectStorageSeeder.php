<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\StorageAttributes;
use RuntimeException;
use SplFileInfo;
use Symfony\Component\Finder\Finder;
use Throwable;

/**
 * Spiegelt den Dateibestand aus `database/seeders/` in den Object Storage
 * (ADR-025/028/045).
 *
 * Der Baum wird 1:1 uebernommen — `database/seeders/products/21/x.jpg` landet
 * unter `products/21/x.jpg`. Keine Umbenennung, keine erfundene Struktur: wer
 * auf der Platte nachsieht, weiss, wie die URL lautet.
 *
 * **Gespiegelt wird alles ausser `*.php`.** Bewusst keine Ordnerliste — eine
 * solche driftet, sobald jemand ein Verzeichnis hinzufuegt, und der Fehler
 * faellt erst auf, wenn im Browser ein Bild fehlt.
 *
 * **Idempotent ueber die Dateigroesse.** Der Bestand wird EINMAL gelistet,
 * danach wird je Datei nur verglichen. Im Dev-Stack laeuft das bei jedem `up`,
 * weil MinIO auf tmpfs liegt (ADR-041); in Produktion einmal.
 *
 * **Gestreamt, nicht eingelesen.** Die Produktbilder sind zusammen 55 MB, die
 * Prospekte 39 MB — `put()` mit Dateiinhalt wuerde das in den Speicher holen.
 */
final class ObjectStorageSeeder extends Seeder
{
    /**
     * Das Quellverzeichnis ist ueberschreibbar, damit der Test gegen einen
     * kleinen Baum laufen kann statt gegen 102 MB.
     */
    public function __construct(private readonly ?string $sourceDirectory = null) {}

    public function run(): void
    {
        $source = $this->sourceDirectory ?? database_path('seeders');

        if (! File::isDirectory($source)) {
            throw new RuntimeException("Quellverzeichnis nicht gefunden: {$source}");
        }

        $disk = Storage::disk('s3');
        $remote = $this->remoteSizes($disk);

        $uploaded = 0;
        $skipped = 0;
        $bytes = 0;

        foreach ($this->localFiles($source) as $file) {
            $key = str_replace(DIRECTORY_SEPARATOR, '/', $file->getRelativePathname());

            if (($remote[$key] ?? null) === $file->getSize()) {
                $skipped++;

                continue;
            }

            $this->upload($disk, $key, $file);

            $uploaded++;
            $bytes += $file->getSize();
        }

        $this->report($uploaded, $skipped, $bytes);
    }

    private function upload(Filesystem $disk, string $key, SplFileInfo $file): void
    {
        $stream = fopen($file->getPathname(), 'rb');

        if ($stream === false) {
            throw new RuntimeException("Nicht lesbar: {$file->getPathname()}");
        }

        try {
            /*
             * ContentType ausdruecklich setzen: ohne ihn liefert MinIO
             * `application/octet-stream`, und der Browser bietet ein Produktbild
             * zum Herunterladen an, statt es anzuzeigen.
             */
            $disk->writeStream($key, $stream, [
                'ContentType' => File::mimeType($file->getPathname()) ?: 'application/octet-stream',
            ]);
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }
    }

    /**
     * Vorhandene Objekte samt Groesse, in EINER Auflistung statt zwei
     * API-Aufrufen je Datei.
     *
     * @return array<string, int>
     */
    private function remoteSizes(Filesystem $disk): array
    {
        try {
            return collect($disk->getDriver()->listContents('', deep: true)->toArray())
                ->filter(fn (StorageAttributes $item): bool => $item->isFile())
                ->mapWithKeys(fn (StorageAttributes $item): array => [$item->path() => $item->fileSize()])
                ->all();
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Object Storage nicht erreichbar ('.$e->getMessage().'). '.
                'Laeuft der Dev-Stack? `docker compose up -d`',
                previous: $e,
            );
        }
    }

    /**
     * @return iterable<SplFileInfo>
     */
    private function localFiles(string $source): iterable
    {
        return Finder::create()
            ->files()
            ->in($source)
            ->notName('*.php')
            ->sortByName();
    }

    private function report(int $uploaded, int $skipped, int $bytes): void
    {
        $megabytes = number_format($bytes / 1024 / 1024, 1);

        $this->command?->getOutput()->writeln(
            "  <fg=gray>Object Storage:</> {$uploaded} hochgeladen ({$megabytes} MB), {$skipped} unveraendert"
        );
    }
}
