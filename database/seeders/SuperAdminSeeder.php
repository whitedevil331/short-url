<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $superAdminRole = DB::selectOne('SELECT id FROM roles WHERE slug = ?', ['superadmin']);

        if (!$superAdminRole) {
            $this->command->error('SuperAdmin role not found. Please run RoleSeeder first.');
            return;
        }

        $email = 'superadmin@example.com';
        $password = Hash::make('password123');

        $existing = DB::selectOne('SELECT id FROM users WHERE email = ?', [$email]);

        if ($existing) {
            DB::statement('UPDATE users SET name = ?, password = ?, role_id = ?, updated_at = datetime("now") 
                WHERE email = ?', ['Super Admin', $password, $superAdminRole->id, $email]);
        } else {
            DB::statement('INSERT INTO users (name, email, password, role_id, created_at, updated_at) 
                VALUES (?, ?, ?, ?, datetime("now"), datetime("now"))', [
                'Super Admin',
                $email,
                $password,
                $superAdminRole->id,
            ]);
        }

    }
}
