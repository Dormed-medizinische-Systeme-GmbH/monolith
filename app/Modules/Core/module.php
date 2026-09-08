<?php

/**
 * Module manifest — single source of truth for this module's purpose and the
 * modules it is allowed to depend on. Read by tests/Architecture/ModuleBoundariesTest.
 *
 * @return array{name: string, description: string, depends_on: list<string>}
 */
return [
    'name' => 'Core',
    'description' => 'Geteilte Domänen-Primitive (Identität/Employee-Zuordnung, gemeinsame Value Objects). Hängt von keinem anderen Modul ab.',
    'depends_on' => [],
];
