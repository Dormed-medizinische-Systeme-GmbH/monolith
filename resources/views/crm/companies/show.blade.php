<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $company->name }}</h2>
            <div class="flex gap-2">
                <a href="{{ route('crm.companies.index') }}" class="text-sm text-gray-600 hover:text-gray-900 self-center">Zurück</a>
                <a href="{{ route('crm.companies.edit', $company) }}"><x-secondary-button>Bearbeiten</x-secondary-button></a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-auth-session-status class="mb-2" :status="session('status')" />

            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-md p-4">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            {{-- Stammdaten --}}
            <section class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-4">Stammdaten</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-gray-500">Name</dt><dd class="text-gray-900">{{ $company->name }}</dd></div>
                    <div><dt class="text-gray-500">Rechtlicher Name</dt><dd class="text-gray-900">{{ $company->legal_name ?? '—' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-gray-500">Notizen</dt><dd class="text-gray-900 whitespace-pre-line">{{ $company->notes ?? '—' }}</dd></div>
                    <div class="sm:col-span-2">
                        <dt class="text-gray-500">Adresse</dt>
                        <dd class="text-gray-900">
                            @if ($company->address)
                                {{ implode(', ', $company->address->toLines()) }}
                            @else
                                <span class="text-gray-400">keine hinterlegt</span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </section>

            {{-- Locations --}}
            <section class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Locations</h3>

                @forelse ($company->locations as $location)
                    <details class="border border-gray-200 rounded-md p-4">
                        <summary class="cursor-pointer text-sm font-medium text-gray-900">
                            {{ $location->name }}
                            <span class="text-gray-400 font-normal">
                                @if ($location->address) — {{ implode(', ', $location->address->toLines()) }} @endif
                            </span>
                        </summary>
                        <form method="POST" action="{{ route('crm.locations.update', $location) }}" class="mt-4 space-y-4">
                            @csrf @method('PUT')
                            <div>
                                <x-input-label :for="'loc_name_'.$location->id" value="Name" />
                                <x-text-input :id="'loc_name_'.$location->id" name="name" type="text" class="mt-1 block w-full" :value="$location->name" required />
                            </div>
                            <div>
                                <x-input-label :for="'loc_notes_'.$location->id" value="Notizen" />
                                <textarea :id="'loc_notes_'.$location->id" name="notes" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ $location->notes }}</textarea>
                            </div>
                            @include('crm._address-fields', ['address' => $location->address])
                            <div class="flex items-center gap-3">
                                <x-primary-button>Speichern</x-primary-button>
                            </div>
                        </form>
                        <form method="POST" action="{{ route('crm.locations.destroy', $location) }}" class="mt-3"
                            onsubmit="return confirm('Location „{{ $location->name }}“ löschen?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-sm text-red-600 hover:text-red-800">Location löschen</button>
                        </form>
                    </details>
                @empty
                    <p class="text-sm text-gray-500">Keine Locations.</p>
                @endforelse

                <details class="border border-dashed border-gray-300 rounded-md p-4">
                    <summary class="cursor-pointer text-sm font-medium text-indigo-600">+ Location hinzufügen</summary>
                    <form method="POST" action="{{ route('crm.companies.locations.store', $company) }}" class="mt-4 space-y-4">
                        @csrf
                        <div>
                            <x-input-label for="new_loc_name" value="Name" />
                            <x-text-input id="new_loc_name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required />
                        </div>
                        <div>
                            <x-input-label for="new_loc_notes" value="Notizen" />
                            <textarea id="new_loc_notes" name="notes" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ old('notes') }}</textarea>
                        </div>
                        @include('crm._address-fields', ['address' => null])
                        <x-primary-button>Hinzufügen</x-primary-button>
                    </form>
                </details>
            </section>

            {{-- Kontakte --}}
            <section class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Kontakte</h3>

                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead><tr class="text-left text-xs uppercase text-gray-500">
                        <th class="py-2">Person</th><th class="py-2">Rolle</th><th class="py-2">Primär</th><th></th>
                    </tr></thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($company->contacts as $contact)
                            <tr>
                                <td class="py-2">
                                    <a href="{{ route('crm.people.show', $contact->person) }}" class="text-indigo-600 hover:underline">{{ $contact->person->full_name }}</a>
                                </td>
                                <td class="py-2 text-gray-600">{{ $contact->role ?? '—' }}</td>
                                <td class="py-2">{{ $contact->is_primary ? 'ja' : '—' }}</td>
                                <td class="py-2 text-right">
                                    <form method="POST" action="{{ route('crm.company-contacts.destroy', $contact) }}"
                                        onsubmit="return confirm('Kontakt entfernen?')">
                                        @csrf @method('DELETE')
                                        <button class="text-red-600 hover:text-red-800">Entfernen</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-3 text-gray-500">Keine Kontakte.</td></tr>
                        @endforelse
                    </tbody>
                </table>

                @if ($assignablePeople->isNotEmpty())
                    <form method="POST" action="{{ route('crm.companies.contacts.store', $company) }}" class="flex flex-wrap items-end gap-3 border-t border-gray-100 pt-4">
                        @csrf
                        <div>
                            <x-input-label for="person_id" value="Person" />
                            <select id="person_id" name="person_id" class="mt-1 border-gray-300 rounded-md shadow-sm">
                                @foreach ($assignablePeople as $person)
                                    <option value="{{ $person->id }}">{{ $person->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="role" value="Rolle" />
                            <x-text-input id="role" name="role" type="text" class="mt-1 block w-40" :value="old('role')" />
                        </div>
                        <label class="flex items-center gap-2 text-sm text-gray-700 pb-2">
                            <input type="checkbox" name="is_primary" value="1" class="rounded border-gray-300"> primär
                        </label>
                        <x-primary-button>Kontakt hinzufügen</x-primary-button>
                    </form>
                @else
                    <p class="text-sm text-gray-500">
                        Keine weiteren Personen verfügbar — <a href="{{ route('crm.people.create') }}" class="text-indigo-600 hover:underline">Person anlegen</a>.
                    </p>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
