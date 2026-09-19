@extends('layouts.app')
@section('content')

<h1 class="text-2xl font-bold text-slate-900">Programación por defecto</h1>
<p class="mt-1 text-sm text-slate-500">Franjas día + horario que aplican a todos los dispositivos. Desde cada dispositivo puedes borrar o reemplazar una franja concreta sin afectar el resto.</p>

<div class="mt-6 rounded-lg border border-slate-200 bg-white p-6 shadow-sm md:w-2/3">
    <h2 class="text-lg font-semibold text-slate-900">Nueva franja</h2>
    <form method="POST" action="{{ route('schedules.store') }}" class="mt-4 space-y-4">
        @csrf
        <div class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700">Perfil</label>
                <select name="profile_id" required class="mt-1 block rounded-md border border-slate-300 px-3 py-2 text-sm">
                    @foreach ($profiles as $profile)
                        <option value="{{ $profile->id }}">{{ $profile->name }} ({{ $profile->dns_mode }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Inicio</label>
                <input type="time" name="start_time" required
                       class="mt-1 block rounded-md border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Fin</label>
                <input type="time" name="end_time" required
                       class="mt-1 block rounded-md border border-slate-300 px-3 py-2 text-sm">
            </div>
        </div>
        <fieldset>
            <legend class="text-sm font-medium text-slate-700">Días</legend>
            <div class="mt-2 flex flex-wrap gap-3">
                @foreach (['Domingo' => 0, 'Lunes' => 1, 'Martes' => 2, 'Miércoles' => 3, 'Jueves' => 4, 'Viernes' => 5, 'Sábado' => 6] as $label => $day)
                    <label class="inline-flex items-center gap-1 text-sm text-slate-700">
                        <input type="checkbox" name="days[]" value="{{ $day }}" class="rounded border-slate-300">
                        {{ $label }}
                    </label>
                @endforeach
            </div>
        </fieldset>
        <button class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-500">Crear franja</button>
    </form>
</div>

<div class="mt-6 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
    @foreach ([1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 0 => 'Domingo'] as $day => $label)
        <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-4 py-3">
                <h3 class="font-semibold text-slate-900">{{ $label }}</h3>
            </div>
            @forelse (($windows[$day] ?? collect()) as $window)
                <div class="flex items-center justify-between px-4 py-3 {{ $window->enabled ? '' : 'opacity-50' }}">
                    <div>
                        <div class="text-sm font-medium text-slate-800">
                            {{ substr($window->start_time, 0, 5) }} – {{ substr($window->end_time, 0, 5) }}
                            <span class="ml-2 rounded-full px-2 py-0.5 text-xs {{ $window->profile->dns_mode === 'off' ? 'bg-slate-100 text-slate-500' : 'bg-emerald-100 text-emerald-700' }}">
                                {{ $window->profile->name }}
                            </span>
                        </div>
                        @if ($window->overrides->count())
                            <div class="text-xs text-amber-600">{{ $window->overrides->count() }} dispositivo(s) con override</div>
                        @endif
                    </div>
                    <div class="flex gap-2">
                        <form method="POST" action="{{ route('schedules.toggle', $window) }}">
                            @csrf
                            <button class="text-xs text-amber-600 hover:underline">{{ $window->enabled ? 'Desactivar' : 'Activar' }}</button>
                        </form>
                        <form method="POST" action="{{ route('schedules.destroy', $window) }}"
                              onsubmit="return confirm('¿Eliminar esta franja de todos los dispositivos?')">
                            @csrf
                            @method('DELETE')
                            <button class="text-xs text-rose-600 hover:underline">Eliminar</button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="px-4 py-4 text-sm text-slate-500">Sin franjas para este día.</p>
            @endforelse
        </div>
    @endforeach
</div>

@endsection
