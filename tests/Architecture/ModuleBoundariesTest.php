<?php

namespace Tests\Architecture;

use FilesystemIterator;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Enforces the modular-monolith boundaries described in
 * .docs/01-architecture/PROJECT_STRUCTURE.md and ADR-005.
 *
 * Each app/Modules/<Name>/module.php declares the modules it may depend on.
 * Domain modules must not reach into the HTTP layer, and may only reference
 * other modules they explicitly declare.
 */
class ModuleBoundariesTest extends TestCase
{
    private const MODULES_PATH = __DIR__.'/../../app/Modules';

    public function test_every_declared_dependency_points_at_an_existing_module(): void
    {
        $modules = $this->manifests();

        foreach ($modules as $module => $dependsOn) {
            foreach ($dependsOn as $dependency) {
                $this->assertNotSame($module, $dependency, "Module [{$module}] must not depend on itself.");
                $this->assertArrayHasKey(
                    $dependency,
                    $modules,
                    "Module [{$module}] declares a dependency on unknown module [{$dependency}].",
                );
            }
        }
    }

    public function test_the_module_dependency_graph_is_acyclic(): void
    {
        $modules = $this->manifests();

        foreach (array_keys($modules) as $start) {
            $stack = [$start];
            $visited = [];

            while ($stack !== []) {
                foreach ($modules[array_pop($stack)] ?? [] as $next) {
                    $this->assertNotSame($start, $next, "Dependency cycle involving module [{$start}].");

                    if (! isset($visited[$next])) {
                        $visited[$next] = true;
                        $stack[] = $next;
                    }
                }
            }
        }
    }

    public function test_modules_only_reference_modules_they_declare(): void
    {
        foreach ($this->manifests() as $module => $dependsOn) {
            $allowed = [$module, ...$dependsOn];

            foreach ($this->phpFilesIn(self::MODULES_PATH."/{$module}") as $file) {
                preg_match_all('/App\\\\Modules\\\\([A-Za-z0-9_]+)/', (string) file_get_contents($file), $matches);

                foreach (array_unique($matches[1]) as $referenced) {
                    $this->assertContains(
                        $referenced,
                        $allowed,
                        sprintf(
                            '%s references App\\Modules\\%s, but module [%s] does not list it in depends_on.',
                            $this->relative($file),
                            $referenced,
                            $module,
                        ),
                    );
                }
            }
        }
    }

    public function test_modules_do_not_depend_on_the_http_layer(): void
    {
        foreach (array_keys($this->manifests()) as $module) {
            foreach ($this->phpFilesIn(self::MODULES_PATH."/{$module}") as $file) {
                $this->assertDoesNotMatchRegularExpression(
                    '/\buse\s+App\\\\Http\\\\/',
                    (string) file_get_contents($file),
                    sprintf('%s imports the HTTP layer; domain modules must stay HTTP-agnostic.', $this->relative($file)),
                );
            }
        }
    }

    /**
     * @return array<string, list<string>> module name => declared dependencies
     */
    private function manifests(): array
    {
        $manifests = [];

        foreach (glob(self::MODULES_PATH.'/*/module.php') ?: [] as $file) {
            /** @var array{depends_on?: list<string>} $manifest */
            $manifest = require $file;
            $manifests[basename(dirname($file))] = $manifest['depends_on'] ?? [];
        }

        $this->assertNotEmpty($manifests, 'No module manifests found under app/Modules.');

        return $manifests;
    }

    /**
     * @return list<string>
     */
    private function phpFilesIn(string $directory): array
    {
        if (! is_dir($directory)) {
            return [];
        }

        $files = [];
        $tree = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
        );

        foreach ($tree as $entry) {
            if ($entry->isFile() && $entry->getExtension() === 'php' && $entry->getFilename() !== 'module.php') {
                $files[] = $entry->getPathname();
            }
        }

        return $files;
    }

    private function relative(string $path): string
    {
        return 'app/Modules/'.ltrim(str_replace(realpath(self::MODULES_PATH) ?: self::MODULES_PATH, '', realpath($path) ?: $path), '/');
    }
}
