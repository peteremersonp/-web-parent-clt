<?php

namespace App\Http\Controllers;

use App\Models\BlacklistRule;
use Illuminate\View\View;

class BlacklistController extends Controller
{
    public function index(): View
    {
        $rules = BlacklistRule::query()
            ->whereNull('device_id')
            ->orderByDesc('updated_at')
            ->get();

        return view('blacklist.index', compact('rules'));
    }
}
