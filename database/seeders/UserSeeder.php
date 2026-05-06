<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ── Admin ─────────────────────────────────────────────
        User::create([
            'name'     => 'Admin',
            'email'    => 'admin@inventoryhub.com',
            'password' => Hash::make('password'),
            'role'     => 'admin',
        ]);

        // ── Staff ─────────────────────────────────────────────
        User::create([
            'name'     => 'Staff One',
            'email'    => 'staff1@inventoryhub.com',
            'password' => Hash::make('password'),
            'role'     => 'staff',
        ]);

        User::create([
            'name'     => 'Staff Two',
            'email'    => 'staff2@inventoryhub.com',
            'password' => Hash::make('password'),
            'role'     => 'staff',
        ]);
    }
}
