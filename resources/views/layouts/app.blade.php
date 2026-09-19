<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'ParentCLT' }} – Panel de control</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-full">
<div class="min-h-full">
    <nav class="bg-slate-900 border-b border-slate-800">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <div class="flex items-center gap-8">
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
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="text-sm text-slate-300 hover:text-white border border-slate-700 rounded-md px-3 py-2">Salir</button>
                </form>
            </div>
        </div>
    </nav>

    <main class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-6 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('new_token'))
                <div class="mb-6 rounded-md border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                    <strong>Nuevo token del dispositivo (cópialo ahora, no se mostrará de nuevo):</strong>
                    <code class="mt-2 block break-all font-mono text-xs">{{ session('new_token') }}</code>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
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
</body>
</html>