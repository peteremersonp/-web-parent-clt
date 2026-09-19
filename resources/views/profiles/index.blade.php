@extends('layouts.app')
@section('content')

<h1 class="text-2xl font-bold text-slate-900">Perfiles de bloqueo</h1>
<p class="mt-1 text-sm text-slate-500">Cada perfil agrupa su propia lista de dominios y su modo DNS. Las franjas de la programación asignan un perfil por día y horario.</p>

<div class="mt-6 rounded-lg border border-slate-200 bg-white p-6 shadow-sm md:w-1/2">
    <h2 class="text-lg font-semibold text-slate-900">Nuevo perfil</h2>
    <form method="POST" action="{{ route('profiles.store') }}" class="mt-4 space-y-3">
        @csrf
        <div>
            <label class="block text-sm font-medium text-slate-700">Nombre</label>
            <input type="text" name="name" required placeholder="Estudio, Domingos, …"
                   class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700">Descripción</label>
            <input type="text" name="description" placeholder="Para qué sirve este perfil"
                   class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700">Modo DNS</label>
            <select name="dns_mode" class="mt-1 block rounded-md border border-slate-300 px-3 py-2 text-sm">
                <option value="local-filter">local-filter (bloquea su lista)</option>
                <option value="off">off (sin filtro, DNS original)</option>
                <option value="block-all">block-all (bloquea TODO el internet)</option>
            </select>
        </div>
        <button class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-500">Crear perfil</button>
    </form>
</div>

<div class="mt-6 overflow-x-auto rounded-lg border border-slate-200 bg-white shadow-sm">
    <table class="min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-50">
            <tr class="text-left text-xs uppercase tracking-wide text-slate-500">
                <th class="px-4 py-3">Perfil</th>
                <th class="px-4 py-3">Modo</th>
                <th class="px-4 py-3">Dominios</th>
                <th class="px-4 py-3">Franjas asignadas</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @foreach ($profiles as $profile)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3">
                        <a href="{{ route('profiles.show', $profile) }}" class="font-medium text-emerald-600 hover:underline">{{ $profile->name }}</a>
                        <div class="text-xs text-slate-400">{{ $profile->description }}</div>
                    </td>
                    <td class="px-4 py-3 text-slate-600">
                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs {{ $profile->dns_mode === 'off' ? 'bg-slate-100 text-slate-500' : ($profile->dns_mode === 'block-all' ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700') }}">
                            {{ $profile->dns_mode }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-slate-600">{{ $profile->rules_count }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $profile->windows_count ?? 0 }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endsection
