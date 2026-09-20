<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login – ParentCLT</title>
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#0f172a">
    <link rel="apple-touch-icon" href="{{ asset('pwa-icons/icon-192.png') }}">
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => navigator.serviceWorker.register('/sw.js'));
        }
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-full bg-slate-900 dark:bg-slate-950">
<div class="flex min-h-full items-center justify-center px-4">
    <div class="w-full max-w-md">
        <div class="mb-6 flex items-center justify-center gap-2 text-white">
            <span class="inline-block h-3 w-3 rounded-full bg-emerald-400"></span>
            <span class="text-2xl font-semibold tracking-wide">ParentCLT</span>
        </div>

        @if (session('status_error'))
            <p class="mb-4 rounded-md border border-red-800 bg-red-900/30 px-4 py-3 text-center text-sm text-red-200">{{ session('status_error') }}</p>
        @endif

        <!-- Principal: Google -->
        <a href="{{ route('google.redirect') }}"
           class="flex w-full items-center justify-center gap-3 rounded-xl bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-xl hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500">
            <svg class="h-5 w-5" viewBox="0 0 24 24">
                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1Z"/>
                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23Z"/>
                <path fill="#FBBC05" d="M5.84 14.09A6.92 6.92 0 0 1 5.5 12c0-.74.12-1.45.34-2.09V7.07H2.18A10.97 10.97 0 0 0 1 12c0 1.77.43 3.45 1.18 4.93l3.66-2.84Z"/>
                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84C6.71 7.31 9.14 5.38 12 5.38Z"/>
            </svg>
            Iniciar sesión con Google
        </a>

        <div class="my-6 flex items-center gap-3">
            <span class="h-px flex-1 bg-slate-700"></span>
            <span class="text-xs text-slate-500">o con contraseña</span>
            <span class="h-px flex-1 bg-slate-700"></span>
        </div>

        <!-- Secundario: email + contraseña -->
        <form method="POST" action="{{ route('login') }}" class="space-y-4 rounded-xl bg-slate-800 p-8 shadow-xl ring-1 ring-slate-700">
            @csrf

            <div>
                <label for="email" class="block text-sm font-medium text-slate-300">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required
                       class="mt-1 block w-full rounded-md border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-white shadow-sm placeholder:text-slate-500 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-slate-300">Contraseña</label>
                <input type="password" name="password" id="password" required
                       class="mt-1 block w-full rounded-md border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-white shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
            </div>

            <label class="flex items-center gap-2 text-sm text-slate-400">
                <input type="checkbox" name="remember" value="1" class="rounded border-slate-600 bg-slate-900">
                Recordarme
            </label>

            @error('email')
                <p class="text-sm text-red-400">{{ $message }}</p>
            @enderror

            <button type="submit"
                    class="w-full rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                Entrar
            </button>
        </form>
    </div>
</div>
@include('partials.install-banner')
</body>
</html>
