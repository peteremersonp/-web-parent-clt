<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\RedirectResponse as ExternalRedirect;

class GoogleAuthController extends Controller
{
    /**
     * Paso 1: enviar al usuario a la pantalla de consentimiento de Google.
     */
    public function redirect(): ExternalRedirect
    {
        $state = Str::random(32);
        session()->put('google_oauth_state', $state);

        $query = http_build_query([
            'client_id' => config('services.google.client_id'),
            'redirect_uri' => $this->redirectUri(),
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'prompt' => 'select_account',
        ]);

        return redirect()->away('https://accounts.google.com/o/oauth2/auth?'.$query);
    }

    /**
     * Paso 2: intercambiar el code por token, leer el email y loguear
     * SOLO si el usuario ya existe en la BD (lista blanca, sin auto-alta).
     */
    public function callback(Request $request): RedirectResponse
    {
        if ($request->input('state') !== session()->pull('google_oauth_state')) {
            return redirect()->route('login')->withErrors(['email' => 'Sesión OAuth inválida: intenta de nuevo.']);
        }

        if ($request->filled('error')) {
            return redirect()->route('login')->withErrors(['email' => 'Autenticación con Google cancelada.']);
        }

        $code = (string) $request->input('code');
        if ($code === '') {
            return redirect()->route('login')->withErrors(['email' => 'Faltó el código de autorización.']);
        }

        $token = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'code' => $code,
            'client_id' => config('services.google.client_id'),
            'client_secret' => config('services.google.client_secret'),
            'redirect_uri' => $this->redirectUri(),
            'grant_type' => 'authorization_code',
        ])->throw()->json();

        $accessToken = $token['access_token'] ?? null;
        if (! $accessToken) {
            Log::warning('google.login.no_token');

            return redirect()->route('login')->withErrors(['email' => 'Google no devolvió el token de acceso.']);
        }

        $info = Http::withToken($accessToken)
            ->get('https://www.googleapis.com/oauth2/v2/userinfo')
            ->throw()->json();

        $email = strtolower((string) ($info['email'] ?? ''));
        $user = $email !== '' ? User::query()->where('email', $email)->first() : null;

        if (! $user) {
            // Sin auto-alta: el email debe existir previamente en la tabla users.
            Log::warning('google.login.rejected', ['email' => $email, 'google_id' => $info['id'] ?? null]);

            return redirect()->route('login')->withErrors(['email' => 'La cuenta '.($email ?: 'desconocida').' no está autorizada para este panel.']);
        }

        Auth::login($user, true);
        Log::info('google.login', ['email' => $user->email, 'google_id' => $info['id'] ?? null]);

        return redirect()->intended(route('dashboard'));
    }

    private function redirectUri(): string
    {
        $configured = config('services.google.redirect');

        return $configured !== null && $configured !== ''
            ? $configured
            : rtrim((string) config('app.url'), '/').'/google/callbak';
    }
}
