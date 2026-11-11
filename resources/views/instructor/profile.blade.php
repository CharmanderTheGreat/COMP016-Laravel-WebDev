<x-app title="Profile">
    <h1 class="mb-6 text-3xl font-bold text-pup-800">My Profile</h1>

    <div class="grid gap-6 md:grid-cols-3">
        {{-- Who am I --}}
        <div class="rounded-2xl border border-base-300 bg-white p-6 text-center shadow-sm">
            <x-avatar :name="$user->name" size="mx-auto h-28 w-28" text="text-4xl" />
            <h2 class="mt-4 text-xl font-bold">{{ $user->name }}</h2>
            <p class="text-sm text-ink/60">Instructor</p>
            <p class="mt-1 break-all text-xs text-ink/50">{{ $user->email }}</p>
        </div>

        <div class="space-y-6 md:col-span-2">
            {{-- Name + email --}}
            <form method="POST" action="{{ route('instructor.profile.update', [], false) }}" class="rounded-2xl border border-base-300 bg-white p-6 shadow-sm">
                @csrf
                @method('PUT')
                <h2 class="mb-4 text-lg font-bold text-pup-800">Account details</h2>

                @if ($errors->any() && ! $errors->hasBag('password'))
                    <div role="alert" class="alert alert-error alert-soft mb-4 text-sm">
                        <ul class="list-disc pl-4">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium" for="name">Name</label>
                        <input id="name" name="name" type="text" class="input w-full" value="{{ old('name', $user->name) }}" required />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium" for="email">Email (used to log in)</label>
                        <input id="email" name="email" type="email" class="input w-full" value="{{ old('email', $user->email) }}" required />
                    </div>
                </div>

                <div class="mt-5 text-right">
                    <button class="btn btn-primary">Save changes</button>
                </div>
            </form>

            {{-- Password --}}
            <form method="POST" action="{{ route('instructor.profile.password', [], false) }}" class="rounded-2xl border border-base-300 bg-white p-6 shadow-sm">
                @csrf
                @method('PUT')
                <h2 class="mb-4 text-lg font-bold text-pup-800">Change password</h2>

                @if ($errors->password->any())
                    <div role="alert" class="alert alert-error alert-soft mb-4 text-sm">
                        <ul class="list-disc pl-4">
                            @foreach ($errors->password->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium" for="current_password">Current password</label>
                        <input id="current_password" name="current_password" type="password" class="input w-full" required />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium" for="password">New password</label>
                        <input id="password" name="password" type="password" class="input w-full" required />
                        <p class="mt-1 text-xs text-ink/55">At least 8 characters with upper and lower case letters, a number and a symbol.</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium" for="password_confirmation">Confirm new password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" class="input w-full" required />
                    </div>
                </div>

                <div class="mt-5 text-right">
                    <button class="btn btn-primary">Change password</button>
                </div>
            </form>
        </div>
    </div>
</x-app>
