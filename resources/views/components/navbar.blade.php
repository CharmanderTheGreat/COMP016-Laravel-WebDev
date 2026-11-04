<div class="navbar bg-base-100 shadow-sm px-4 sticky top-0 z-30">

    <div class="navbar-start">
        <div class="dropdown">
            <div tabindex="0" role="button" class="btn btn-ghost lg:hidden" aria-label="Open menu">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8m-8 6h16" />
                </svg>
            </div>
            <ul tabindex="0" class="menu menu-sm dropdown-content bg-base-100 rounded-box z-30 mt-3 w-56 p-2 shadow">
                <li><a href="/instructor/attendance/create" @class(['menu-active' => request()->is('instructor/attendance/create')])>Record Attendance</a></li>
                <li><a href="/instructor/attendance" @class(['menu-active' => request()->is('instructor/attendance') || request()->is('instructor/attendance/20*')])>Attendance List</a></li>
                <li><a href="/instructor/students" @class(['menu-active' => request()->is('instructor/students*')])>Students</a></li>
            </ul>
        </div>
        <a href="/instructor/dashboard" class="btn btn-ghost text-xl font-bold text-primary">Idea</a>
    </div>

    <div class="navbar-center hidden lg:flex">
        <ul class="menu menu-horizontal gap-1 px-1">
            <li><a href="/instructor/attendance/create" @class(['menu-active' => request()->is('instructor/attendance/create')])>Record Attendance</a></li>
            <li><a href="/instructor/attendance" @class(['menu-active' => request()->is('instructor/attendance') || request()->is('instructor/attendance/20*')])>Attendance List</a></li>
            <li><a href="/instructor/students" @class(['menu-active' => request()->is('instructor/students*')])>Students</a></li>
        </ul>
    </div>

    <div class="navbar-end gap-2">
        @guest
            <a href="/register" class="btn btn-ghost">Register</a>
            <a href="/login" class="btn btn-primary">Login</a>
        @endguest

        @auth
            <span class="hidden sm:inline text-sm text-base-content/60">{{ auth()->user()->name }}</span>
            <form method="POST" action="/logout">
                @csrf
                @method("DELETE")
                <button class="btn btn-ghost btn-sm">Log Out</button>
            </form>
        @endauth
    </div>
</div>
