<?php

namespace App\Actions\Setting;

use App\Http\Requests\Setting\SettingCreateRequest;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;

class SettingCreateAction
{
    public function __construct()
    {
    }

    public function handle(SettingCreateRequest $request): Setting
    {
        $data = $request->validated();

        Log::debug('Validation passed successfully');

        return Setting::create($data);
    }
}
