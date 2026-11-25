<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = trim((string) env('DRYPP_ADMIN_EMAIL', ''));
        $name = trim((string) env('DRYPP_ADMIN_NAME', 'Drypp Administrator'));
        $password = (string) env('DRYPP_ADMIN_PASSWORD', '');

        // Do not create an account with a built-in/default password.
        if ($email === '' || $password === '') {
            $this->command?->warn('Admin seeding skipped. Set DRYPP_ADMIN_EMAIL and DRYPP_ADMIN_PASSWORD in .env, then rerun AdminUserSeeder.');
            return;
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('DRYPP_ADMIN_EMAIL must be a valid email address.');
        }

        if (strlen($password) < 12) {
            throw new \InvalidArgumentException('DRYPP_ADMIN_PASSWORD must contain at least 12 characters.');
        }

        $admin = User::firstOrNew(['email' => Str::lower($email)]);
        $admin->name = $name !== '' ? $name : 'Drypp Administrator';
        $admin->role = 'admin';
        $admin->email_verified_at ??= now();

        // Only set/change the password when explicitly supplied in the environment.
        $admin->password = Hash::make($password);
        $admin->save();

        $this->command?->info("Administrator account ensured for {$admin->email}.");
    }
}
