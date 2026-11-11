<x-auth title="Log in" heading="Welcome back" subtitle="Log in to see your attendance.">
    <form action="/login" method="POST" class="space-y-4">
        @csrf

        @if ($errors->any())
            <div role="alert" class="alert alert-error alert-soft text-sm">{{ $errors->first() }}</div>
        @endif

        {{-- Students type their ID number; the instructor (who has no ID) types their email.
             Both go in the same "login" field and SessionsController decides which one it is. --}}
        <div>
            <label class="mb-1 block text-sm font-medium" for="login">ID number or email</label>
            <input id="login" name="login" type="text" class="input w-full"
                   placeholder="2024-00482-SR-0" value="{{ old('login') }}" required autofocus />
            <p class="mt-1 text-xs text-ink/55">Students: use your ID number. Instructor: use your email.</p>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium" for="password">Password</label>
            <input id="password" name="password" type="password" class="input w-full" required />
        </div>

        <label class="flex cursor-pointer items-center gap-2 text-sm">
            <input type="checkbox" name="remember" class="checkbox checkbox-sm checkbox-primary" />
            Remember me
        </label>

        <button class="btn btn-primary w-full">Log in</button>

        <p class="text-center text-sm text-ink/70">
            New student? <a href="/register" class="font-semibold text-pup-700 hover:underline">Sign up</a>
        </p>
        <p class="text-center text-xs text-ink/50">
            Forgot your password? Ask your instructor for a temporary one.
        </p>
    </form>
</x-auth>
