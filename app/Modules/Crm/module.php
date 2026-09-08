<?php

/**
 * Module manifest — single source of truth for this module's purpose and the
 * modules it is allowed to depend on. Read by tests/Architecture/ModuleBoundariesTest.
 *
 * @return array{name: string, description: string, depends_on: list<string>}
 */
return [
    'name' => 'Crm',
    'description' => 'Stammdaten & Beziehungen: Company, Person, CompanyContact, Address, Location.',
    'depends_on' => ['Core'],
];
