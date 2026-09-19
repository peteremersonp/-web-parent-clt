@extends('layouts.app')
@section('content')

<h1 class="text-2xl font-bold text-slate-900">Dispositivos</h1>
<p class="mt-1 text-sm text-slate-500">{{ $devices->count() }} registrados por el agente.</p>

@if ($devices->isEmpty())
    <div class="mt-8 rounded-lg border border-dashed border-slate-300 bg-white p-10 text-center text-sm text-slate-500">
        Todavía no hay ningún dispositivo registrado.
    </div>
@else
    <div class="mt-6 overflow-x-auto rounded-lg border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50">
                <tr class="text-left text-xs uppercase tracking-wide text-slate-500">
                    <th class="px-4 py-3">Nombre</th>
                    <th class="px-4 py-3">OS</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Última conexión</th>
                    <th class="px-4 py-3">Versión aplicada</th>
                    <th class="px-4 py-3">Reglas</th>
                    <th class="px-4 py-3">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($devices as $device)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-medium text-slate-900">
                            <a href="{{ route('devices.show', $device) }}" class="text-emerald-600 hover:underline">{{ $device->name }}</a>
                            <a href="{{ route('devices.schedule', $device) }}" class="ml-2 text-xs text-amber-600 hover:underline">Programación</a>
                            <div class="text-xs text-slate-400 font-mono">{{ substr($device->machine_id, 0, 8) }}…</div>
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $device->os_version ?? '—' }}</td>
                        <td class="px-4 py-3">@include('partials.status-badge', ['status' => $device->status])</td>
                        <td class="px-4 py-3 text-slate-600">{{ $device->last_seen_at ? $device->last_seen_at->diffForHumans() : '—' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $device->applied_version ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $device->blacklist_rules_count }}</td>
                        <td class="px-4 py-3">
                            <div class="flex flex-col gap-1">
                                <form method="POST" action="{{ route('devices.token', $device) }}" class="inline">
                                    @csrf
                                    <button class="text-xs text-amber-600 hover:underline">Regenerar token</button>
                                </form>
                                <form method="POST" action="{{ route('devices.destroy', $device) }}" class="inline"
                                      onsubmit="return confirm('¿Eliminar este dispositivo? Esta acción borra sus reglas.')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-xs text-rose-600 hover:underline">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

@endsection