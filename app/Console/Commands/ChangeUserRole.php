<?php

namespace App\Console\Commands;

use App\Models\User;
use Exception;
use Illuminate\Console\Command;

class ChangeUserRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:set-role {user} {role}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'set user role';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $userId = $this->argument('user');

        $roleName = $this->argument('role');

        $user = User::findOrFail($userId);

        try {
            $user->update(['role' => $roleName]);

            $this->info("User {$user->name} successfully changed role: {$roleName}");
        } catch (Exception $e) {
            $this->error("Error: Role '{$roleName}' undefined");
        }
    }
}
