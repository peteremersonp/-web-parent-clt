@extends('layouts.app')
@section('content')

<div class="flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Perfil: {{ $profile->name }}</h1>
        <p class="mt-1 text-sm text-slate-500">{{ $profile->description ?? 'Sin descripción' }}</p>
    </div>
    <a href="{{ route('profiles.index') }}" class="text-sm text-slate-500 hover:text-slate-700">← Volver a perfiles</a>
</div>

<div class="mt-6 grid gap-6 lg:grid-cols-2">
    <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900">Ajustes del perfil</h2>
        <form method="POST" action="{{ route('profiles.update', $profile) }}" class="mt-4 space-y-3">
            @csrf
            @method('PATCH')
            <div>
                <label class="block text-sm font-medium text-slate-700">Nombre</label>
                <input type="text" name="name" value="{{ $profile->name }}" required
                       class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Descripción</label>
                <input type="text" name="description" value="{{ $profile->description }}"
                       class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Modo DNS</label>
                <select name="dns_mode" class="mt-1 block rounded-md border border-slate-300 px-3 py-2 text-sm">
                    <option value="local-filter" @selected($profile->dns_mode === 'local-filter')>local-filter (bloquea su lista)</option>
                    <option value="off" @selected($profile->dns_mode === 'off')>off (sin filtro, DNS original)</option>
                    <option value="block-all" @selected($profile->dns_mode === 'block-all')>block-all (bloquea TODO el internet)</option>
                    <option value="allow-only" @selected($profile->dns_mode === 'allow-only')>allow-only (bloquea TODO excepto su lista)</option>
                </select>
            </div>
            <button class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-500">Guardar</button>
        </form>
        <form method="POST" action="{{ route('profiles.destroy', $profile) }}" class="mt-6 border-t border-slate-100 pt-4"
              onsubmit="return confirm('¿Eliminar el perfil y todas sus reglas? Las franjas que lo usen quedarán sin perfil.')">
            @csrf
            @method('DELETE')
            <button class="text-sm text-rose-600 hover:underline">Eliminar perfil</button>
        </form>
    </div>

    <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900">Dominios permitidos (modo allow-only)</h2>
        <p class="mt-1 text-sm text-slate-500">Con el modo <strong>allow-only</strong> se bloquea TODO el internet excepto estos dominios y sus subdominios. Útil para niños pequeños o modo examen.</p>
        <div class="mt-2 rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800">
            ⚠️ Sin bloqueo de firewall, un usuario avanzado puede saltarse este modo cambiando el DNS manualmente.
        </div>
        <form method="POST" action="{{ route('profiles.allows.store', $profile) }}" class="mt-4 flex flex-wrap items-end gap-2">
            @csrf
            <div class="flex-1 min-w-48">
                <label class="block text-sm font-medium text-slate-700">Dominio permitido</label>
                <input type="text" name="domain" required placeholder="colegiodominio.com"
                       class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
            </div>
            <button class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">Permitir</button>
        </form>

        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr class="text-left text-xs uppercase tracking-wide text-slate-500">
                        <th class="px-4 py-3">Dominio</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($profile->allows as $allow)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-mono text-slate-800">{{ $allow->domain }} <span class="text-xs text-slate-400">+ subdominios</span></td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2 py-0.5 text-xs {{ $allow->enabled ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $allow->enabled ? 'activo' : 'inactivo' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex gap-3">
                                    <form method="POST" action="{{ route('profiles.allows.toggle', [$profile, $allow]) }}">
                                        @csrf
                                        <button class="text-xs text-amber-600 hover:underline">{{ $allow->enabled ? 'Desactivar' : 'Activar' }}</button>
                                    </form>
                                    <form method="POST" action="{{ route('profiles.allows.destroy', [$profile, $allow]) }}"
                                          onsubmit="return confirm('¿Eliminar este dominio permitido?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-xs text-rose-600 hover:underline">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-4 py-6 text-center text-slate-500">Sin dominios permitidos (con allow-only se bloquearía TODO).</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900">Dominios bloqueados por este perfil</h2>
        <form method="POST" action="{{ route('profiles.rules.store', $profile) }}" class="mt-4 flex flex-wrap items-end gap-2">
            @csrf
            <div class="flex-1 min-w-48">
                <label class="block text-sm font-medium text-slate-700">Dominio</label>
                <input type="text" name="domain" required placeholder="facebook.com o *.tiktok.com"
                       class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Tipo</label>
                <select name="type" class="mt-1 block rounded-md border border-slate-300 px-3 py-2 text-sm">
                    <option value="exact">exact</option>
                    <option value="wildcard">wildcard</option>
                </select>
            </div>
            <button class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-500">Añadir</button>
        </form>

        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr class="text-left text-xs uppercase tracking-wide text-slate-500">
                        <th class="px-4 py-3">Dominio</th>
                        <th class="px-4 py-3">Tipo</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($profile->rules as $rule)
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
                                    <form method="POST" action="{{ route('profiles.rules.toggle', [$profile, $rule]) }}">
                                        @csrf
                                        <button class="text-xs text-amber-600 hover:underline">{{ $rule->enabled ? 'Desactivar' : 'Activar' }}</button>
                                    </form>
                                    <form method="POST" action="{{ route('profiles.rules.destroy', [$profile, $rule]) }}"
                                          onsubmit="return confirm('¿Eliminar regla del perfil?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-xs text-rose-600 hover:underline">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-6 text-center text-slate-500">Este perfil todavía no bloquea ningún dominio.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
