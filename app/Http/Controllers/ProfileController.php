<?php

namespace App\Http\Controllers;

use App\Models\BlacklistProfile;
use App\Models\BlacklistProfileRule;
use App\Services\PolicyService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function index(): View
    {
        $profiles = BlacklistProfile::query()->withCount('rules')->orderBy('name')->get();

        return view('profiles.index', compact('profiles'));
    }

    public function store(Request $request, PolicyService $policy): RedirectResponse
    {
        $data = $this->validated($request);

        $profile = BlacklistProfile::create([
            ...$data,
            'slug' => BlacklistProfile::slugify($data['name']),
            'enabled' => true,
        ]);

        $policy->bumpAllDevices();
        auditLogAsJson('profile.created', $profile);

        return redirect()->route('profiles.show', $profile)->with('status', 'Perfil creado. Añade sus dominios bloqueados.');
    }

    public function show(BlacklistProfile $profile): View
    {
        $profile->load('rules');

        return view('profiles.show', compact('profile'));
    }

    public function update(Request $request, BlacklistProfile $profile, PolicyService $policy): RedirectResponse
    {
        $profile->update($this->validated($request));
        $policy->bumpAllDevices();
        auditLogAsJson('profile.updated', $profile);

        return back()->with('status', 'Perfil actualizado.');
    }

    public function destroy(BlacklistProfile $profile, PolicyService $policy): RedirectResponse
    {
        auditLogAsJson('profile.deleted', $profile);
        $profile->delete();
        $policy->bumpAllDevices();

        return redirect()->route('profiles.index')->with('status', 'Perfil eliminado.');
    }

    public function storeRule(Request $request, BlacklistProfile $profile, PolicyService $policy): RedirectResponse
    {
        $data = $request->validate([
            'domain' => 'required|string|max:255',
            'type' => 'required|in:exact,wildcard',
        ]);

        $rule = $profile->rules()->create([...$data, 'enabled' => true]);
        $policy->bumpAllDevices();
        auditLogAsJson('profile.rule.created', $rule);

        return back()->with('status', 'Regla añadida al perfil.');
    }

    public function toggleRule(BlacklistProfile $profile, BlacklistProfileRule $rule, PolicyService $policy): RedirectResponse
    {
        $rule->update(['enabled' => ! $rule->enabled]);
        $policy->bumpAllDevices();
        auditLogAsJson('profile.rule.toggled', $rule);

        return back()->with('status', 'Regla '.($rule->enabled ? 'activada' : 'desactivada').'.');
    }

    public function destroyRule(BlacklistProfile $profile, BlacklistProfileRule $rule, PolicyService $policy): RedirectResponse
    {
        auditLogAsJson('profile.rule.deleted', $rule);
        $rule->delete();
        $policy->bumpAllDevices();

        return back()->with('status', 'Regla eliminada del perfil.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'dns_mode' => 'required|in:local-filter,off,block-all',
        ]);
    }
}
