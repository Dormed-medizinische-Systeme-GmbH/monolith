@props(['person', 'action', 'method' => 'POST'])

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <x-input-label for="first_name" value="Vorname" />
            <x-text-input id="first_name" name="first_name" type="text" class="mt-1 block w-full"
                :value="old('first_name', $person->first_name)" required autofocus />
            <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="last_name" value="Nachname" />
            <x-text-input id="last_name" name="last_name" type="text" class="mt-1 block w-full"
                :value="old('last_name', $person->last_name)" required />
            <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="email" value="E-Mail" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                :value="old('email', $person->email)" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="phone" value="Telefon" />
            <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full"
                :value="old('phone', $person->phone)" />
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>
    </div>

    <div>
        <x-input-label for="notes" value="Notizen" />
        <textarea id="notes" name="notes" rows="3"
            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('notes', $person->notes) }}</textarea>
        <x-input-error :messages="$errors->get('notes')" class="mt-2" />
    </div>

    <div class="flex items-center gap-4">
        <x-primary-button>Speichern</x-primary-button>
        <a href="{{ url()->previous() }}" class="text-sm text-gray-600 hover:text-gray-900">Abbrechen</a>
    </div>
</form>
