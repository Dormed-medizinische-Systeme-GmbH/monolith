<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Company bearbeiten — {{ $company->name }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                @include('crm.companies._form', [
                    'company' => $company,
                    'action' => route('crm.companies.update', $company),
                    'method' => 'PUT',
                ])
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('crm.companies.destroy', $company) }}"
                    onsubmit="return confirm('Company „{{ $company->name }}“ wirklich löschen?')">
                    @csrf
                    @method('DELETE')
                    <x-danger-button>Company löschen</x-danger-button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
