<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRuleRequest;
use App\Models\BlacklistRule;
use App\Services\PolicyService;
use Illuminate\Http\RedirectResponse;

class RuleController extends Controller
{
    public function store(StoreRuleRequest $request, PolicyService $policy): RedirectResponse
    {
        $rule = BlacklistRule::create([
            'device_id' => $request->input('device_id') ?: null,
            'domain' => $request->input('domain'),
            'type' => $request->input('type'),
            'enabled' => true,
        ]);

        $policy->bumpAllDevices();
        auditLogAsJson('rule.created', $rule);

        return back()->with('status', 'Regla creada.');
    }

    public function toggle(BlacklistRule $rule, PolicyService $policy): RedirectResponse
    {
        $rule->update(['enabled' => ! $rule->enabled]);
        $policy->bumpAllDevices();
        auditLogAsJson('rule.toggled', $rule);

        return back()->with('status', 'Regla '.($rule->enabled ? 'activada' : 'desactivada').'.');
    }

    public function destroy(BlacklistRule $rule, PolicyService $policy): RedirectResponse
    {
        auditLogAsJson('rule.deleted', $rule);

        $rule->delete();
        $policy->bumpAllDevices();

        return back()->with('status', 'Regla eliminada.');
    }
}
