<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sin conexión – ParentCLT</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-full bg-slate-900 dark:bg-slate-950">
<div class="flex min-h-full items-center justify-center px-4">
    <div class="max-w-md text-center">
        <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-slate-800">
            <svg class="h-8 w-8 text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376C8.505 15.103 14.495 15.103 19.303 16.139M11.999 7.5h.008v.008h-.008V7.5Zm.375 0v.016" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M21.021 12.483A9 9 0 1 1 3.28 5.04" />
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-white">Sin conexión</h1>
        <p class="mt-3 text-sm text-slate-400">
            No hay internet en este momento. La última programación sigue aplicando;
            el panel volverá a cargar cuando haya conexión.
        </p>
        <a href="/dashboard" class="mt-6 inline-block rounded-md bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-500">Reintentar</a>
    </div>
</div>
</body>
</html>
