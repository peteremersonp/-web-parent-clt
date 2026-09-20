@php
    $map = [
        App\Models\Device::STATUS_NEVER => ['Never', 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300'],
        App\Models\Device::STATUS_ONLINE => ['Online', 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300'],
        App\Models\Device::STATUS_DEGRADED => ['Degradado', 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300'],
        App\Models\Device::STATUS_OFFLINE => ['Offline', 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300'],
    ];
    [$label, $classes] = $map[$status] ?? ['?', 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300'];
@endphp
<span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $classes }}">{{ $label }}</span>
