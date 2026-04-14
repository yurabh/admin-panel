<?php

namespace App\Jobs;

use App\Enums\UserRole;
use App\Models\User;
use App\Notifications\AdminLoginAlertNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class NotifyAdminsAboutLoginJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public User $user) {}

    public function handle(): void
    {
        User::query()
            ->where('role', UserRole::ADMIN)
            ->where('id', '!=', $this->user->id)
            ->chunkById(100, function ($admins) {
                foreach ($admins as $admin) {
                    $admin->notify(new AdminLoginAlertNotification($this->user->name));
                }
            });
    }

    public function backoff(): array
    {
        return [10, 10, 10];
    }
}
