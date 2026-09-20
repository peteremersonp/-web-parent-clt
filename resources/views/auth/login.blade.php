<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login – ParentCLT</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class' };
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>
</head>
<body class="h-full bg-slate-900 dark:bg-slate-950">
<div class="flex min-h-full items-center justify-center px-4">
    <div class="w-full max-w-md">
        <div class="mb-6 flex items-center justify-center gap-2 text-white">
            <span class="inline-block h-3 w-3 rounded-full bg-emerald-400"></span>
            <span class="text-2xl font-semibold tracking-wide">ParentCLT</span>
        </div>

        <form method="POST" action="{{ route('login') }}" class="space-y-4 rounded-xl bg-slate-800 p-8 shadow-xl ring-1 ring-slate-700">
            @csrf

            <div>
                <label for="email" class="block text-sm font-medium text-slate-300">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
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
</body>
</html>
