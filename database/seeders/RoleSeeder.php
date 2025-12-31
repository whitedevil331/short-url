<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'SuperAdmin', 'slug' => 'superadmin'],
            ['name' => 'Admin', 'slug' => 'admin'],
            ['name' => 'Member', 'slug' => 'member'],
            ['name' => 'Sales', 'slug' => 'sales'],
            ['name' => 'Manager', 'slug' => 'manager'],
        ];

        foreach ($roles as $role) {
            DB::statement('INSERT OR IGNORE INTO roles (name, slug, created_at, updated_at) 
                VALUES (?, ?, datetime("now"), datetime("now"))', [$role['name'], $role['slug']]);
        }
    }
}
