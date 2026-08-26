<?php

namespace Database\Seeders;

use App\Enums\ApprovalStatus;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CoreRoleSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');

        if (! $email || ! $password) {
            return;
        }

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => env('ADMIN_NAME', 'C-Net Store Administrator'),
                'phone' => env('ADMIN_PHONE', '7782801846'),
                'password' => Hash::make($password),
                'role' => UserRole::SuperAdmin,
                'status' => ApprovalStatus::Approved,
                'email_verified_at' => now(),
            ],
        );
    }
}

