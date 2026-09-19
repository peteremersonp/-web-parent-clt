@extends('layouts.app')
@section('content')

<div class="flex items-center justify-between">
    <div>
        <a href="{{ route('devices.index') }}" class="text-sm text-emerald-600 hover:underline">← Dispositivos</a>
        <h1 class="mt-1 text-2xl font-bold text-slate-900">{{ $device->name }}</h1>
        <p class="mt-1 text-sm text-slate-500 font-mono">{{ $device->machine_id }}</p>
    </div>
    <div class="flex items-center gap-3">
        @include('partials.status-badge', ['status' => $device->status])
        <details class="relative">
            <summary class="cursor-pointer rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 hover:bg-slate-50">
                Ver última política enviada
            </summary>
            <div class="absolute right-0 z-10 mt-2 max-h-96 w-96 overflow-auto rounded-md border border-slate-200 bg-slate-900 p-4 shadow-xl">
                <pre class="text-xs text-emerald-300 font-mono whitespace-pre-wrap">{{ json_encode($lastPolicy, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
            </div>
        </details>
    </div>
</div>

<div class="mt-6 grid gap-6 lg:grid-cols-3">
    <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm lg:col-span-1">
        <h2 class="text-lg font-semibold text-slate-900">Datos</h2>
        <dl class="mt-4 space-y-3 text-sm">
            <div class="flex justify-between">
                <dt class="text-slate-500">OS</dt>
                <dd class="text-slate-800">{{ $device->os_version ?? '—' }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-slate-500">Última conexión</dt>
                <dd class="text-slate-800">{{ $device->last_seen_at ? $device->last_seen_at->diffForHumans() : '—' }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-slate-500">Versión aplicada</dt>
                <dd class="text-slate-800">{{ $device->applied_version ?? '—' }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-slate-500">Versión de política</dt>
                <dd class="text-slate-800">{{ $device->policy_version }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-slate-500">Registrado</dt>
                <dd class="text-slate-800">{{ $device->created_at->format('d/m/Y H:i') }}</dd>
            </div>
        </dl>

        <form method="POST" action="{{ route('devices.update', $device) }}" class="mt-6 border-t border-slate-100 pt-4">
            @csrf
            @method('PATCH')
            <label for="name" class="block text-sm font-medium text-slate-700">Renombrar</label>
            <div class="mt-1 flex gap-2">
                <input type="text" name="name" id="name" value="{{ $device->name }}" required maxlength="255"
                       class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
                <button class="rounded-md bg-slate-800 px-3 py-2 text-sm font-medium text-white hover:bg-slate-700">Guardar</button>
            </div>
        </form>

        <div class="mt-4 flex gap-2 border-t border-slate-100 pt-4">
            <form method="POST" action="{{ route('devices.token', $device) }}">
                @csrf
                <button class="rounded-md bg-amber-500 px-3 py-2 text-sm font-medium text-white hover:bg-amber-400">Regenerar token</button>
            </form>
            <form method="POST" action="{{ route('devices.destroy', $device) }}"
                  onsubmit="return confirm('¿Eliminar este dispositivo? Se borran también sus reglas.')">
                @csrf
                @method('DELETE')
                <button class="rounded-md bg-rose-600 px-3 py-2 text-sm font-medium text-white hover:bg-rose-500">Eliminar</button>
            </form>
        </div>
    </div>

    <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">
        <h2 class="text-lg font-semibold text-slate-900">Reglas específicas de este dispositivo</h2>
        <p class="mt-1 text-xs text-slate-500">Se suman a las reglas globales para este equipo.</p>

        <form method="POST" action="{{ route('rules.store') }}" class="mt-4 flex flex-wrap items-end gap-2">
            @csrf
            <input type="hidden" name="device_id" value="{{ $device->id }}">
            <div class="flex-1 min-w-48">
                <label for="domain" class="block text-sm font-medium text-slate-700">Dominio</label>
                <input type="text" name="domain" id="domain" required placeholder="tiktok.com o *.tiktok.com"
                       class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
            </div>
            <div>
                <label for="type" class="block text-sm font-medium text-slate-700">Tipo</label>
                <select name="type" id="type" class="mt-1 block rounded-md border border-slate-300 px-3 py-2 text-sm">
                    <option value="exact">exact</option>
                    <option value="wildcard">wildcard</option>
                </select>
            </div>
            <button class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-500">Añadir</button>
        </form>

        @if ($device->blacklistRules->isEmpty())
            <p class="mt-6 text-sm text-slate-500">Sin reglas específicas.</p>
        @else
            <table class="mt-4 w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 text-left text-xs uppercase tracking-wide text-slate-500">
                        <th class="py-2 pr-4">Dominio</th>
                        <th class="py-2 pr-4">Tipo</th>
                        <th class="py-2 pr-4">Estado</th>
                        <th class="py-2">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($device->blacklistRules as $rule)
                        <tr>
                            <td class="py-2 pr-4 font-mono text-slate-800">{{ $rule->domain }}</td>
                            <td class="py-2 pr-4 text-slate-600">{{ $rule->type }}</td>
                            <td class="py-2 pr-4">
                                <span class="inline-flex rounded-full px-2 py-0.5 text-xs {{ $rule->enabled ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $rule->enabled ? 'activa' : 'inactiva' }}
                                </span>
                            </td>
                            <td class="py-2">
                                <div class="flex gap-3">
                                    <form method="POST" action="{{ route('rules.toggle', $rule) }}">
                                        @csrf
                                        <button class="text-xs text-amber-600 hover:underline">{{ $rule->enabled ? 'Desactivar' : 'Activar' }}</button>
                                    </form>
                                    <form method="POST" action="{{ route('rules.destroy', $rule) }}"
                                          onsubmit="return confirm('¿Eliminar regla?')">
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
        @endif
    </div>
</div>

@endsection