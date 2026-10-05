{{-- PLACEHOLDER: the instructor dev replaces this whole file. --}}
<x-layout title="Instructor Dashboard">
    <div class="max-w-5xl mx-auto px-4">
        <h1 class="text-2xl font-bold mb-4">Instructor Dashboard (coming soon)</h1>
        <form action="/logout" method="POST">
            @csrf
            <button class="btn btn-sm">Log out</button>
        </form>
    </div>
</x-layout>