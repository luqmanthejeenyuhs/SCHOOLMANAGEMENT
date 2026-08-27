<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateSuperAdmin extends Command
{
    protected $signature = 'admin:create-super';

    protected $description = 'Create the platform super admin (belongs to no school) — safe to run on production, prompts interactively so no password ever touches .env or shell history.';

    public function handle(): int
    {
        if (User::where('role', 'super_admin')->exists()) {
            $this->warn('A super_admin already exists. Refusing to create a second one from this command.');
            $this->line('If you genuinely need another, do it through the app itself once logged in, or via php artisan tinker.');

            return self::FAILURE;
        }

        $name = $this->ask('Full name');
        $email = $this->ask('Email address');
        $password = $this->secret('Password (input hidden, min 10 characters)');
        $confirm = $this->secret('Confirm password');

        $validator = Validator::make(
            ['name' => $name, 'email' => $email, 'password' => $password],
            [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:10',
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        if ($password !== $confirm) {
            $this->error('Passwords did not match.');

            return self::FAILURE;
        }

        User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'super_admin',
        ]);

        $this->info("Super admin created: {$email}");
        $this->line('Log in at /login with this account, then use "Impersonate" from /superadmin/schools to manage any school.');

        return self::SUCCESS;
    }
}
