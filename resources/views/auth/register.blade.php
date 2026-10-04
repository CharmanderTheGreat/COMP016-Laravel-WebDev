<x-layout>
    <form action="/register" method="POST">
        @csrf

        <fieldset class="fieldset bg-base-200 border-base-300 rounded-box w-xs border p-4 mx-auto">
            <legend class="fieldset-legend">Login</legend>

            <label class="label">Email</label>
            <input name="name" type="text" class="input" placeholder="Name" />

            <label class="label">Email</label>
            <input name="email" type="email" class="input" placeholder="Email" />

            <label class="label">Password</label>
            <input name="password" type="password" class="input" placeholder="Password" />

            <label class="label">Role</label>
            <select name="role" class="select select-bordered w-full">
                <option disabled selected>Role</option>
                <option value="student">Student</option>
                <option value="instructor">Instructor</option>
            </select>

            <button class="btn btn-neutral mt-4">Login</button>
        </fieldset>

    </form>

</x-layout>
