<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $person->full_name }}</h2>
            <div class="flex gap-2">
                <a href="{{ route('crm.people.index') }}" class="text-sm text-gray-600 hover:text-gray-900 self-center">Zurück</a>
                <a href="{{ route('crm.people.edit', $person) }}"><x-secondary-button>Bearbeiten</x-secondary-button></a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-auth-session-status class="mb-2" :status="session('status')" />

            <section class="bg-white shadow-sm sm:rounded-lg p-6">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-gray-500">E-Mail</dt><dd class="text-gray-900">{{ $person->email ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">Telefon</dt><dd class="text-gray-900">{{ $person->phone ?? '—' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-gray-500">Notizen</dt><dd class="text-gray-900 whitespace-pre-line">{{ $person->notes ?? '—' }}</dd></div>
                </dl>
            </section>

            <section class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-4">Companies</h3>
                @forelse ($person->contacts as $contact)
                    <div class="flex items-center justify-between py-2 text-sm border-b border-gray-100 last:border-0">
                        <a href="{{ route('crm.companies.show', $contact->company) }}" class="text-indigo-600 hover:underline">{{ $contact->company->name }}</a>
                        <span class="text-gray-500">{{ $contact->role ?? '—' }}{{ $contact->is_primary ? ' · primär' : '' }}</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">Keiner Company zugeordnet.</p>
                @endforelse
            </section>
        </div>
    </div>
</x-app-layout>
