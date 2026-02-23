<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create roles
        $superadmin = Role::create(['nama' => 'superadmin']);
        $admin      = Role::create(['nama' => 'admin']);

        // Create default superadmin user
        User::create([
            'role_id'  => $superadmin->id,
            'username' => 'superadmin',
            'password' => Hash::make('password'),
            'status'   => 'aktif',
        ]);

        // Create default admin user
        User::create([
            'role_id'  => $admin->id,
            'username' => 'admin',
            'password' => Hash::make('password'),
            'status'   => 'aktif',
        ]);
    }
}
