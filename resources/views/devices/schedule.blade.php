@extends('layouts.app')
@section('content')

<div class="flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Programación de {{ $device->name }}</h1>
        <p class="mt-1 text-sm text-slate-500">Franjas que aplican a este dispositivo: hereda la programación por defecto, con sus overrides aplicados.</p>
    </div>
    <div class="flex gap-4 text-sm">
        <a href="{{ route('schedules.index') }}" class="text-slate-500 hover:text-slate-700">← Programación por defecto</a>
        <a href="{{ route('devices.show', $device) }}" class="text-emerald-600 hover:underline">Ver dispositivo</a>
    </div>
</div>

<div class="mt-6 rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
    <h2 class="text-lg font-semibold text-slate-900">Vista efectiva (lo que recibe el agente)</h2>
    <p class="mt-1 text-sm text-slate-500">{{ $effective->count() }} franja(s) activa(s) tras los overrides.</p>
    <div class="mt-4 overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50">
                <tr class="text-left text-xs uppercase tracking-wide text-slate-500">
                    <th class="px-4 py-3">Día</th>
                    <th class="px-4 py-3">Horario</th>
                    <th class="px-4 py-3">Perfil</th>
                    <th class="px-4 py-3">Modo</th>
                    <th class="px-4 py-3">Dominios</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($effective as $w)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-slate-700">{{ \App\Models\ScheduleWindow::dayName($w['day_of_week']) }}</td>
                        <td class="px-4 py-3 font-mono text-slate-800">{{ $w['start_time'] }} – {{ $w['end_time'] }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $w['profile']['name'] }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs {{ $w['profile']['dns_mode'] === 'off' ? 'bg-slate-100 text-slate-500' : 'bg-emerald-100 text-emerald-700' }}">
                                {{ $w['profile']['dns_mode'] }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ count($w['profile']['blacklist']) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">Este dispositivo no tiene programación (usa la blacklist global heredada).</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-6 rounded-lg border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-100 px-4 py-3">
        <h2 class="text-lg font-semibold text-slate-900">Overrides de este dispositivo</h2>
        <p class="text-sm text-slate-500">Borra o reemplaza franjas concretas de la programación por defecto solo para {{ $device->name }}.</p>
    </div>
    <div class="divide-y divide-slate-100">
        @foreach ($windows as $window)
            @php
                $override = $window->overrides->first();
                $dayLabel = \App\Models\ScheduleWindow::dayName($window->day_of_week);
            @endphp
            <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-3 {{ $window->enabled ? '' : 'opacity-50' }}">
                <div class="text-sm">
                    <span class="font-medium text-slate-800">{{ $dayLabel }} {{ substr($window->start_time, 0, 5) }}–{{ substr($window->end_time, 0, 5) }}</span>
                    <span class="ml-2 rounded-full px-2 py-0.5 text-xs {{ $window->profile->dns_mode === 'off' ? 'bg-slate-100 text-slate-500' : 'bg-emerald-100 text-emerald-700' }}">
                        {{ $window->profile->name }}
                    </span>
                    @if ($override)
                        <span class="ml-2 rounded-full px-2 py-0.5 text-xs bg-amber-100 text-amber-800">
                            override: {{ $override->action === 'delete' ? 'borrada' : '→ '.$override->profile?->name }}
                        </span>
                    @endif
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    @if ($override)
                        <form method="POST" action="{{ route('devices.schedule.override.destroy', [$device, $window]) }}">
                            @csrf
                            @method('DELETE')
                            <button class="text-xs text-slate-600 hover:underline">Quitar override</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('devices.schedule.override', [$device, $window]) }}">
                            @csrf
                            <input type="hidden" name="action" value="delete">
                            <button class="text-xs text-rose-600 hover:underline">Borrar esta franja solo para este dispositivo</button>
                        </form>
                        <form method="POST" action="{{ route('devices.schedule.override', [$device, $window]) }}" class="flex items-center gap-1">
                            @csrf
                            <input type="hidden" name="action" value="replace">
                            <select name="profile_id" class="rounded-md border border-slate-300 px-2 py-1 text-xs">
                                @foreach ($profiles as $profile)
                                    <option value="{{ $profile->id }}">{{ $profile->name }}</option>
                                @endforeach
                            </select>
                            <button class="text-xs text-amber-600 hover:underline">Reemplazar por…</button>
                        </form>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>

@endsection
