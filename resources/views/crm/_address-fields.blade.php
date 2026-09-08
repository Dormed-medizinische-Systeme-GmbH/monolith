@props(['address' => null])

<fieldset class="grid grid-cols-1 gap-4 sm:grid-cols-6">
    <legend class="text-sm font-medium text-gray-700">Adresse <span class="text-gray-400">(optional)</span></legend>

    <div class="sm:col-span-4">
        <x-input-label for="address_street" value="Straße" />
        <x-text-input id="address_street" name="address[street]" type="text" class="mt-1 block w-full"
            :value="old('address.street', $address?->street)" />
        <x-input-error :messages="$errors->get('address.street')" class="mt-2" />
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="address_house_number" value="Hausnr." />
        <x-text-input id="address_house_number" name="address[house_number]" type="text" class="mt-1 block w-full"
            :value="old('address.house_number', $address?->house_number)" />
        <x-input-error :messages="$errors->get('address.house_number')" class="mt-2" />
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="address_postal_code" value="PLZ" />
        <x-text-input id="address_postal_code" name="address[postal_code]" type="text" class="mt-1 block w-full"
            :value="old('address.postal_code', $address?->postal_code)" />
        <x-input-error :messages="$errors->get('address.postal_code')" class="mt-2" />
    </div>

    <div class="sm:col-span-3">
        <x-input-label for="address_city" value="Ort" />
        <x-text-input id="address_city" name="address[city]" type="text" class="mt-1 block w-full"
            :value="old('address.city', $address?->city)" />
        <x-input-error :messages="$errors->get('address.city')" class="mt-2" />
    </div>

    <div class="sm:col-span-1">
        <x-input-label for="address_country_code" value="Land" />
        <x-text-input id="address_country_code" name="address[country_code]" type="text" maxlength="2"
            class="mt-1 block w-full uppercase" :value="old('address.country_code', $address?->country_code ?? 'DE')" />
        <x-input-error :messages="$errors->get('address.country_code')" class="mt-2" />
    </div>
</fieldset>
