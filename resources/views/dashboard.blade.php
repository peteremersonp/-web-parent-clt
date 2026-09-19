@extends('layouts.app')
@section('content')

<h1 class="text-2xl font-bold text-slate-900">Dashboard</h1>
<p class="mt-1 text-sm text-slate-500">Resumen del estado de los dispositivos y reglas activas.</p>

<div class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 p-4 shadow-sm flex items-center justify-between">
    <div>
        <h2 class="font-semibold text-emerald-900">Instalador del agente (Windows)</h2>
        <p class="text-sm text-emerald-700">Última compilación con perfiles y programación horaria incluidos.</p>
    </div>
    <a href="{{ route('downloads.setup') }}"
       class="inline-flex items-center gap-2 rounded-md bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-500">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
        </svg>
        Descargar ParentCLT-Setup.exe
    </a>
</div>

<div class="mt-6 grid grid-cols-2 gap-4 md:grid-cols-5">
    @php
        $cards = [
            'Total' => $counts['total'],
            'Online' => $counts['online'],
            'Degradado' => $counts['degraded'],
            'Offline' => $counts['offline'],
            'Never' => $counts['never'],
        ];
        $colors = [
            'Total' => 'bg-slate-50 text-slate-900',
            'Online' => 'bg-emerald-50 text-emerald-900',
            'Degradado' => 'bg-amber-50 text-amber-900',
            'Offline' => 'bg-rose-50 text-rose-900',
            'Never' => 'bg-slate-100 text-slate-700',
        ];
    @endphp
    @foreach ($cards as $label => $value)
        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-sm text-slate-500">{{ $label }}@if ($label === 'Online') <span class="text-xs">(last_seen < 5 min)</span>@endif</p>
            <p class="mt-1 text-3xl font-bold {{ $colors[$label] }}">{{ $value }}</p>
        </div>
    @endforeach
</div>

<div class="mt-8 rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
    <h2 class="text-lg font-semibold text-slate-900">Top 10 reglas activas</h2>
    @if ($topRules->isEmpty())
        <p class="mt-3 text-sm text-slate-500">No hay reglas activas.</p>
    @else
        <table class="mt-4 w-full text-sm">
            <thead>
                <tr class="border-b border-slate-200 text-left text-xs uppercase tracking-wide text-slate-500">
                    <th class="py-2 pr-4">Dominio</th>
                    <th class="py-2 pr-4">Reglas</th>
                    <th class="py-2">Alcance</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($topRules as $rule)
                    <tr>
                        <td class="py-2 pr-4 font-mono text-slate-800">{{ $rule->domain }}</td>
                        <td class="py-2 pr-4 text-slate-700">{{ $rule->total }}</td>
                        <td class="py-2 text-slate-500">
                            @if ($rule->device_scoped === $rule->total)
                                Solo dispositivos
                            @elseif ($rule->device_scoped === 0)
                                Global
                            @else
                                Global + dispositivos
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

@endsection