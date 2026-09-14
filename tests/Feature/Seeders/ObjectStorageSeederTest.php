<?php

declare(strict_types=1);

use Database\Seeders\ObjectStorageSeeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/*
| Geprueft wird gegen einen kleinen Baum im Temp-Verzeichnis, nicht gegen die
| echten 102 MB — die Logik ist dieselbe, die Laufzeit nicht.
*/

beforeEach(function (): void {
    Storage::fake('s3');

    $this->source = sys_get_temp_dir().'/seeder-probe-'.bin2hex(random_bytes(4));

    File::ensureDirectoryExists($this->source.'/products/21');
    File::put($this->source.'/logo.png', 'ein-logo');
    File::put($this->source.'/products/21/cover.jpg', 'ein-produktbild');
    File::put($this->source.'/DatabaseSeeder.php', '<?php // kein Asset');
});

afterEach(function (): void {
    File::deleteDirectory($this->source);
});

test('der Baum wird 1:1 gespiegelt', function (): void {
    (new ObjectStorageSeeder($this->source))->run();

    Storage::disk('s3')->assertExists('logo.png');
    Storage::disk('s3')->assertExists('products/21/cover.jpg');

    expect(Storage::disk('s3')->get('products/21/cover.jpg'))->toBe('ein-produktbild');
});

test('PHP-Dateien bleiben aussen vor', function (): void {
    (new ObjectStorageSeeder($this->source))->run();

    Storage::disk('s3')->assertMissing('DatabaseSeeder.php');
});

test('ein zweiter Lauf laedt nichts erneut hoch', function (): void {
    (new ObjectStorageSeeder($this->source))->run();

    $before = Storage::disk('s3')->lastModified('logo.png');

    (new ObjectStorageSeeder($this->source))->run();

    expect(Storage::disk('s3')->lastModified('logo.png'))->toBe($before);
});

test('eine geaenderte Datei wird ersetzt', function (): void {
    (new ObjectStorageSeeder($this->source))->run();

    File::put($this->source.'/logo.png', 'ein-deutlich-laengeres-logo');

    (new ObjectStorageSeeder($this->source))->run();

    expect(Storage::disk('s3')->get('logo.png'))->toBe('ein-deutlich-laengeres-logo');
});

test('ein fehlendes Quellverzeichnis bricht mit klarer Meldung ab', function (): void {
    expect(fn () => (new ObjectStorageSeeder($this->source.'/gibt-es-nicht'))->run())
        ->toThrow(RuntimeException::class, 'Quellverzeichnis nicht gefunden');
});
