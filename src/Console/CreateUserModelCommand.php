<?php

namespace InertiaResource\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class CreateUserModelCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vue-admin-panel:make-user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new user instance with all fields and password confirmation';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('👤 Create a new user');
        $this->newLine();

        // Get User model class from config
        $userModelClass = config('inertia-resource.user_model', \App\Models\User::class);

        if (!class_exists($userModelClass)) {
            $this->error("User model class '{$userModelClass}' does not exist.");
            $this->comment('Please ensure your User model exists or update config/inertia-resource.php');
            return 1;
        }

        // Prompt for user fields
        $name = $this->ask('Name');
        $email = $this->ask('Email');
        $password = $this->secret('Password');
        $passwordConfirmation = $this->secret('Confirm Password');

        // Validate inputs
        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $passwordConfirmation,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if ($validator->fails()) {
            $this->newLine();
            $this->error('Validation failed:');
            foreach ($validator->errors()->all() as $error) {
                $this->error('  - ' . $error);
            }
            return 1;
        }

        // Ask for email verification
        $emailVerified = $this->confirm('Mark email as verified?', false);

        // Create the user
        try {
            $user = $userModelClass::create([
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'email_verified_at' => $emailVerified ? now() : null,
            ]);

            $this->newLine();
            $this->info('✅ User created successfully!');
            $this->newLine();
            $this->table(
                ['Field', 'Value'],
                [
                    ['ID', $user->id],
                    ['Name', $user->name],
                    ['Email', $user->email],
                    ['Email Verified', $user->email_verified_at ? 'Yes' : 'No'],
                    ['Created At', $user->created_at],
                ]
            );

            return 0;
        } catch (\Exception $e) {
            $this->newLine();
            $this->error('Failed to create user: ' . $e->getMessage());
            return 1;
        }
    }
}

