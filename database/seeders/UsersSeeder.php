<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            ['name' => 'Administrador', 'password' => Hash::make('admin123'), 'email_verified_at' => now()]
        );
        $admin->assignRole('admin');

        $operador = User::firstOrCreate(
            ['email' => 'operador@sistema.com'],
            ['name' => 'João Operador', 'password' => Hash::make('operador123'), 'email_verified_at' => now()]
        );
        $operador->assignRole('operador');

        $estoquista = User::firstOrCreate(
            ['email' => 'estoque@sistema.com'],
            ['name' => 'Maria Estoquista', 'password' => Hash::make('estoque123'), 'email_verified_at' => now()]
        );
        $estoquista->assignRole('estoquista');
    }
}
