@props(['company', 'action', 'method' => 'POST'])

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div>
        <x-input-label for="name" value="Name" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
            :value="old('name', $company->name)" required autofocus />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="legal_name" value="Rechtlicher Name" />
        <x-text-input id="legal_name" name="legal_name" type="text" class="mt-1 block w-full"
            :value="old('legal_name', $company->legal_name)" />
        <x-input-error :messages="$errors->get('legal_name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="notes" value="Notizen" />
        <textarea id="notes" name="notes" rows="3"
            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('notes', $company->notes) }}</textarea>
        <x-input-error :messages="$errors->get('notes')" class="mt-2" />
    </div>

    @include('crm._address-fields', ['address' => $company->address])

    <div class="flex items-center gap-4">
        <x-primary-button>Speichern</x-primary-button>
        <a href="{{ url()->previous() }}" class="text-sm text-gray-600 hover:text-gray-900">Abbrechen</a>
    </div>
</form>
