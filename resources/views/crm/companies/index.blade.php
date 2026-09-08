<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Companies</h2>
            <a href="{{ route('crm.companies.create') }}">
                <x-primary-button>Neue Company</x-primary-button>
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="GET" class="flex gap-2">
                <x-text-input name="q" type="search" class="block w-full sm:w-80" placeholder="Name suchen …"
                    :value="$q" />
                <x-secondary-button type="submit">Suchen</x-secondary-button>
            </form>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ort</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kontakte</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($companies as $company)
                            <tr>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                    <a href="{{ route('crm.companies.show', $company) }}" class="hover:underline">{{ $company->name }}</a>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $company->address?->city ?? '—' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $company->contacts_count }}</td>
                                <td class="px-6 py-4 text-right text-sm">
                                    <a href="{{ route('crm.companies.edit', $company) }}" class="text-indigo-600 hover:text-indigo-900">Bearbeiten</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500">Keine Companies.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $companies->links() }}
        </div>
    </div>
</x-app-layout>
