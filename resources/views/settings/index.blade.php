@extends('layouts.app')
@section('content')

<h1 class="text-2xl font-bold text-slate-900">Ajustes</h1>
<p class="mt-1 text-sm text-slate-500">Guardar aquí incrementa <code class="font-mono text-xs">policy_version</code> en todos los dispositivos.</p>

<form method="POST" action="{{ route('settings.update') }}" class="mt-6 space-y-5 rounded-lg border border-slate-200 bg-white p-6 shadow-sm md:w-2/3">
    @csrf

    <div class="grid gap-5 md:grid-cols-2">
        <div>
            <label for="poll_interval_sec" class="block text-sm font-medium text-slate-700">Intervalo de poll (segundos)</label>
            <input type="number" name="poll_interval_sec" id="poll_interval_sec" value="{{ $settings['poll_interval_sec'] }}"
                   min="30" max="86400" required
                   class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
            <p class="mt-1 text-xs text-slate-500">Entre 30 y 86400 (240 = 4 minutos). Valor semilla: 600.</p>
        </div>

        <div>
            <label for="dns_mode" class="block text-sm font-medium text-slate-700">Modo DNS</label>
            <select name="dns_mode" id="dns_mode" class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                <option value="local-filter" @selected($settings['dns_mode'] === 'local-filter')>local-filter</option>
                <option value="off" @selected($settings['dns_mode'] === 'off')>off</option>
            </select>
            <p class="mt-1 text-xs text-slate-500">local-filter: filtrar en el PC; off: DNS directo.</p>
        </div>

        <div>
            <label for="upstream_doh" class="block text-sm font-medium text-slate-700">DNS-over-HTTPS upstream</label>
            <input type="url" name="upstream_doh" id="upstream_doh" value="{{ $settings['upstream_doh'] ?? '' }}"
                   class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
        </div>

        <div>
            <label for="fallback_dns" class="block text-sm font-medium text-slate-700">DNS de respaldo (IP)</label>
            <input type="text" name="fallback_dns" id="fallback_dns" value="{{ $settings['fallback_dns'] ?? '' }}"
                   class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
        </div>
    </div>

    <button class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">Guardar ajustes</button>
</form>

@endsection