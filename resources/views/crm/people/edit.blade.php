<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Person bearbeiten — {{ $person->full_name }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                @include('crm.people._form', [
                    'person' => $person,
                    'action' => route('crm.people.update', $person),
                    'method' => 'PUT',
                ])
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('crm.people.destroy', $person) }}"
                    onsubmit="return confirm('Person „{{ $person->full_name }}“ wirklich löschen?')">
                    @csrf
                    @method('DELETE')
                    <x-danger-button>Person löschen</x-danger-button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
