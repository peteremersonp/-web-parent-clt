<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-100 dark:bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'ParentCLT' }} – Panel de control</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class' };
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>
    <style>
        /* Modo dark global: oscurece los patrones comunes de todas las vistas
           (las clases con .dark delante ganan especificidad a las de Tailwind). */
        .dark .bg-white { background-color: rgb(15 23 42); }
        .dark .bg-slate-50 { background-color: rgb(30 41 59); }
        .dark .bg-slate-100 { background-color: rgb(30 41 59); }
        .dark .text-slate-900 { color: rgb(241 245 249); }
        .dark .text-slate-800 { color: rgb(226 232 240); }
        .dark .text-slate-700 { color: rgb(203 213 225); }
        .dark .text-slate-600 { color: rgb(148 163 184); }
        .dark .text-slate-500 { color: rgb(148 163 184); }
        .dark .border-slate-200 { border-color: rgb(30 41 59); }
        .dark .border-slate-300 { border-color: rgb(51 65 85); }
        .dark .border-slate-100 { border-color: rgb(30 41 59); }
        .dark .divide-slate-100 > :not([hidden]) ~ :not([hidden]) { border-color: rgb(30 41 59); }
        .dark .divide-slate-200 > :not([hidden]) ~ :not([hidden]) { border-color: rgb(30 41 59); }
        .dark .hover\:bg-slate-50:hover { background-color: rgb(30 41 59); }
        .dark .hover\:bg-slate-800\/60:hover { background-color: rgb(30 41 59); }
        .dark input, .dark select, .dark textarea {
            background-color: rgb(15 23 42); color: rgb(226 232 240); border-color: rgb(51 65 85);
        }
        .dark code { color: rgb(148 163 184); }
        .dark .bg-emerald-50 { background-color: rgb(6 78 59); }
        .dark .text-emerald-900 { color: rgb(209 250 229); }
        .dark .text-emerald-700 { color: rgb(110 231 183); }
        .dark .text-emerald-800 { color: rgb(167 243 208); }
        .dark .text-emerald-600 { color: rgb(52 211 153); }
        .dark .hover\:text-emerald-700:hover { color: rgb(110 231 183); }
        .dark .border-emerald-200 { border-color: rgb(6 78 59); }
        .dark .bg-amber-50 { background-color: rgb(120 53 15); }
        .dark .text-amber-900 { color: rgb(254 243 199); }
        .dark .text-amber-700 { color: rgb(251 191 36); }
        .dark .text-amber-800 { color: rgb(253 230 138); }
        .dark .text-amber-600 { color: rgb(245 158 11); }
        .dark .text-rose-600 { color: rgb(251 113 133); }
        .dark .text-rose-700 { color: rgb(251 113 133); }
        .dark .text-rose-900 { color: rgb(254 205 211); }
        .dark .text-red-800 { color: rgb(254 202 202); }
        .dark .border-amber-200 { border-color: rgb(146 64 14); }
        .dark .border-red-200 { border-color: rgb(127 29 29); }
        .dark .border-rose-200 { border-color: rgb(159 18 57); }
        .dark .bg-rose-50 { background-color: rgb(76 5 25); }
        .dark .bg-red-50 { background-color: rgb(76 5 25); }
        .dark .text-indigo-700 { color: rgb(165 180 252); }
        .dark .bg-indigo-100 { background-color: rgb(49 46 129); }
    </style>
</head>
<body class="h-full bg-slate-100 text-slate-800 dark:bg-slate-950 dark:text-slate-200">
<div class="min-h-full">
    <nav class="bg-slate-900 border-b border-slate-800">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <div class="flex items-center gap-4 md:gap-8">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2 text-white font-semibold tracking-wide">
                        <span class="inline-block h-3 w-3 rounded-full bg-emerald-400"></span>
                        ParentCLT
                    </a>
                    <div class="hidden md:flex items-center gap-1 text-sm">
                        <a href="{{ route('dashboard') }}" class="px-3 py-2 rounded-md {{ request()->routeIs('dashboard') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:text-white' }}">Dashboard</a>
                        <a href="{{ route('devices.index') }}" class="px-3 py-2 rounded-md {{ request()->routeIs('devices.*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:text-white' }}">Dispositivos</a>
                        <a href="{{ route('blacklist.index') }}" class="px-3 py-2 rounded-md {{ request()->routeIs('blacklist.*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:text-white' }}">Blacklist global</a>
                        <a href="{{ route('profiles.index') }}" class="px-3 py-2 rounded-md {{ request()->routeIs('profiles.*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:text-white' }}">Perfiles</a>
                        <a href="{{ route('schedules.index') }}" class="px-3 py-2 rounded-md {{ request()->routeIs('schedules.*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:text-white' }}">Programación</a>
                        <a href="{{ route('settings.index') }}" class="px-3 py-2 rounded-md {{ request()->routeIs('settings.*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:text-white' }}">Ajustes</a>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="toggleTheme()" title="Modo claro/oscuro"
                            class="rounded-md border border-slate-700 p-2 text-slate-300 hover:text-white hover:border-slate-500">
                        <svg class="h-4 w-4 dark:hidden" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
                        </svg>
                        <svg class="hidden h-4 w-4 dark:block" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                        </svg>
                    </button>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="text-sm text-slate-300 hover:text-white border border-slate-700 rounded-md px-3 py-2">Salir</button>
                    </form>
                    <button onclick="document.getElementById('mobile-menu').classList.toggle('hidden')" class="md:hidden rounded-md border border-slate-700 p-2 text-slate-300 hover:text-white">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        <div id="mobile-menu" class="hidden md:hidden border-t border-slate-800 px-4 pb-4 pt-2">
            <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded-md text-sm {{ request()->routeIs('dashboard') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:text-white' }}">Dashboard</a>
            <a href="{{ route('devices.index') }}" class="block px-3 py-2 rounded-md text-sm {{ request()->routeIs('devices.*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:text-white' }}">Dispositivos</a>
            <a href="{{ route('blacklist.index') }}" class="block px-3 py-2 rounded-md text-sm {{ request()->routeIs('blacklist.*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:text-white' }}">Blacklist global</a>
            <a href="{{ route('profiles.index') }}" class="block px-3 py-2 rounded-md text-sm {{ request()->routeIs('profiles.*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:text-white' }}">Perfiles</a>
            <a href="{{ route('schedules.index') }}" class="block px-3 py-2 rounded-md text-sm {{ request()->routeIs('schedules.*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:text-white' }}">Programación</a>
            <a href="{{ route('settings.index') }}" class="block px-3 py-2 rounded-md text-sm {{ request()->routeIs('settings.*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:text-white' }}">Ajustes</a>
        </div>
    </nav>

    <main class="py-6 md:py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-6 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-200">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('new_token'))
                <div class="mb-6 rounded-md border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:border-amber-700 dark:bg-amber-900/30 dark:text-amber-200">
                    <strong>Nuevo token del dispositivo (cópialo ahora, no se mostrará de nuevo):</strong>
                    <code class="mt-2 block break-all font-mono text-xs">{{ session('new_token') }}</code>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-800 dark:bg-red-900/30 dark:text-red-200">
                    <strong>Errores:</strong>
                    <ul class="mt-1 list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </div>
    </main>
</div>
<script>
    function toggleTheme() {
        const html = document.documentElement;
        const dark = html.classList.toggle('dark');
        localStorage.theme = dark ? 'dark' : 'light';
    }
    const menu = document.getElementById('mobile-menu');
    menu.addEventListener('click', (e) => {
        if (e.target.tagName === 'A') menu.classList.add('hidden');
    });
</script>
</body>
</html>
