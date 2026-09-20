<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Usuarios autorizados a usar el panel via Google OAuth.
 * El callback de Google NO crea usuarios: solo inicia sesión si el email
 * ya existe aquí (lista blanca explícita, sin auto-registro).
 */
class GoogleUsersSeeder extends Seeder
{
    public const USERS = [
        'peter.emerson.p@gmail.com' => 'Peter Emerson',
        'diana.marcela.velezlemos@gmail.com' => 'Diana Marcela Vélez Lemos',
        'moneco84@gmail.com' => 'Moneco',
    ];

    public function run(): void
    {
        foreach (self::USERS as $email => $name) {
            User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    // Sin login por contraseña: la contraseña es aleatoria e inutilizable.
                    'password' => Hash::make(Str::random(48)),
                ],
            );
        }
    }
}
