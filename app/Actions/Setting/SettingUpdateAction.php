<?php

namespace App\Actions\Setting;

use App\Http\Requests\Setting\SettingUpdateRequest;
use App\Jobs\NotifyUserAboutSettingsChangeJob;
use App\Models\Setting;
use App\Models\User;
use Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SettingUpdateAction
{
    /**
     * @throws \Throwable
     */
    public function handle(SettingUpdateRequest $request, Setting $setting): Setting
    {
        $data = $request->only('value');

        Log::debug('Validation passed', ['id' => $setting->id]);

        $setting = DB::transaction(function () use ($setting, $data) {
            $setting->update($data);

            return $setting;
        });

        Log::debug('Setting updated', ['setting' => $setting->id]);

        $jobs = $this->verifiedAuthUserJobsArray($setting);

        $this->triggerBatch($jobs, $setting);

        return $setting;
    }

    private function verifiedAuthUserJobsArray(Setting $setting): array
    {
        $jobs = [];
        User::query()->whereNotNull('email_verified_at')
            ->chunkById(3, function ($users) use (&$jobs, $setting) {
                foreach ($users as $user) {
                    $jobs[] = new NotifyUserAboutSettingsChangeJob($user, $setting->key);
                }
            });

        return $jobs;
    }

    /**
     * @throws \Throwable
     */
    private function triggerBatch(array $jobs, Setting $setting): void
    {
        if (! empty($jobs)) {
            Bus::batch($jobs)
                ->name("Broadcast update: {$setting->key}")
                ->then(function ($batch) {
                    Log::info("Sending  {$batch->id} successfully done!");
                })
                ->catch(function ($batch, $e) {
                    Log::error('Error during sending: '.$e->getMessage());
                })
                ->finally(function ($batch) {})
                ->dispatch();
        }
    }
}
