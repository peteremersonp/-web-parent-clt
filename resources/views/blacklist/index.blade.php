@extends('layouts.app')
@section('content')

<h1 class="text-2xl font-bold text-slate-900">Blacklist global</h1>
<p class="mt-1 text-sm text-slate-500">Reglas que aplican a todos los dispositivos. Cada cambio incrementa la versión de política.</p>

<div class="mt-6 rounded-lg border border-slate-200 bg-white p-6 shadow-sm md:w-1/2">
    <h2 class="text-lg font-semibold text-slate-900">Nueva regla</h2>
    <form method="POST" action="{{ route('rules.store') }}" class="mt-4 flex flex-wrap items-end gap-2">
        @csrf
        <div class="flex-1 min-w-48">
            <label for="domain" class="block text-sm font-medium text-slate-700">Dominio</label>
            <input type="text" name="domain" id="domain" required placeholder="facebook.com o *.tiktok.com"
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
</div>

<div class="mt-6 rounded-lg border border-slate-200 bg-white shadow-sm">
    @if ($rules->isEmpty())
        <p class="p-6 text-sm text-slate-500">No hay reglas globales todavía.</p>
    @else
        <table class="w-full text-sm">
            <thead class="bg-slate-50">
                <tr class="text-left text-xs uppercase tracking-wide text-slate-500">
                    <th class="px-4 py-3">Dominio</th>
                    <th class="px-4 py-3">Tipo</th>
                    <th class="px-4 py-3">Estado</th>
                    <th class="px-4 py-3">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($rules as $rule)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-mono text-slate-800">{{ $rule->domain }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $rule->type }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs {{ $rule->enabled ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                {{ $rule->enabled ? 'activa' : 'inactiva' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
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

@endsection