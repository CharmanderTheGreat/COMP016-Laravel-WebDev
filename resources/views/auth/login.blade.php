<x-layout>
    <form action="/login" method="POST">
        @csrf

        <fieldset class="fieldset bg-base-200 border-base-300 rounded-box w-xs border p-4 mx-auto">
            <legend class="fieldset-legend">Login</legend>

            <label class="label">Email</label>
            <input name="email" type="email" class="input" placeholder="Email" />

            <label class="label">Password</label>
            <input name="password" type="password" class="input" placeholder="Password" />

            <button class="btn btn-neutral mt-4">Login</button>
        </fieldset>

    </form>

</x-layout>
