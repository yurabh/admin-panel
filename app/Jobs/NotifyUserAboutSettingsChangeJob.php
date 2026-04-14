<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\SettingChangedNotification;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotifyUserAboutSettingsChangeJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public User $user, public string $settingKey) {}

    public function handle(): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $this->user->notify(new SettingChangedNotification($this->settingKey));
    }
}
