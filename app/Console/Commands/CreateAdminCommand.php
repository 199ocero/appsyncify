<?php

namespace App\Console\Commands;

use App\Models\AdminUser;

use Illuminate\Console\Command;

use function Laravel\Prompts\info;
use function Laravel\Prompts\text;
use function Laravel\Prompts\password;

class CreateAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:admin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create admin user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = text(
            label: 'What is your name?',
            placeholder: 'E.g. John Doe',
            required: 'Name is required.',
            validate: fn (string $value) => match (true) {
                strlen($value) < 3 => 'The name must be at least 3 characters.',
                strlen($value) > 255 => 'The name must not exceed 255 characters.',
                default => null
            }
        );

        $email = text(
            label: 'What is your email?',
            placeholder: 'E.g. admin@example.com',
            required: 'Email is required.',
            validate: fn (string $value) => match (true) {
                !filter_var($value, FILTER_VALIDATE_EMAIL) => 'The email must be a valid email address.',
                AdminUser::where('email', $value)->exists() => 'The email already exists.',
                default => null
            }
        );

        $password = password(
            label: 'What is your password?',
            placeholder: 'e.g. password',
            required: 'Password is required.',
            validate: fn (string $value) => match (true) {
                strlen($value) < 8 => 'The password must be at least 8 characters.',
                default => null
            }
        );

        AdminUser::create([
            'name' => $name,
            'email' => $email,
            'password' => $password
        ]);

        info('Admin created successfully!');
    }
}
