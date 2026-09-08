<?php

namespace App\Http\Requests\Crm;

/**
 * Companies carry no unique fields, so the update rules match the store rules.
 */
class UpdateCompanyRequest extends StoreCompanyRequest {}
