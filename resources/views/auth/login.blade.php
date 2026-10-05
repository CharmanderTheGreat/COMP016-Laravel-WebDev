<x-layout title="Log in">
    <form action="/login" method="POST" class="mx-auto max-w-sm px-4">
        @csrf

        <fieldset class="fieldset bg-base-200 border-base-300 rounded-box border p-4">
            <legend class="fieldset-legend">Log in</legend>

            @if ($errors->any())
                <div class="alert alert-error mb-2">{{ $errors->first() }}</div>
            @endif

            {{-- Students type their ID number; the instructor (who has no ID)
                 types their email. Both go in the same "login" field and
                 SessionsController decides which one it is. --}}
            <label class="label" for="login">ID number or email</label>
            <input id="login" name="login" type="text" class="input w-full"
                   placeholder="2024-00482-SR-0" value="{{ old('login') }}" required autofocus />
            <p class="label text-xs">Students: use your ID number. Instructor: use your email.</p>

            <label class="label" for="password">Password</label>
            <input id="password" name="password" type="password" class="input w-full" required />

            <label class="label mt-2">
                <input type="checkbox" name="remember" class="checkbox checkbox-sm" /> Remember me
            </label>

            <button class="btn btn-neutral mt-4">Log in</button>

            <p class="text-sm text-center mt-2">
                New student? <a href="/register" class="link">Sign up</a>
            </p>
        </fieldset>
    </form>
</x-layout>