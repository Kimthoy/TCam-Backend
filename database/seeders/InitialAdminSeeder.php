<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class InitialAdminSeeder extends Seeder
{
    public function run()
    {
        $email = env('INITIAL_ADMIN_EMAIL', 'admin@example.com');
        $password = env('INITIAL_ADMIN_PASSWORD', 'ChangeMe123!');

        if (! User::where('email', $email)->exists()) {
            $user = User::create([
                'name' => 'Super Admin',
                'email' => $email,
                'password' => Hash::make($password),
                'role' => 'superadmin',
                'is_active' => true,
            ]);

            // If Sanctum installed, create a personal access token and show it in console
            if (method_exists($user, 'createToken')) {
                $token = $user->createToken('initial-admin-token', ['*'])->plainTextToken;
                $this->command->info("Initial admin created: {$email}");
                $this->command->info("Initial admin token (store securely): {$token}");
            } else {
                $this->command->info("Initial admin created: {$email} (Sanctum not available for token creation).");
            }
        } else {
            $this->command->info("Admin user already exists: {$email}");
        }
    }
}
