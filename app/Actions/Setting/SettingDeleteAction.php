<?php

namespace App\Actions\Setting;

use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class SettingDeleteAction
{
    public function __construct()
    {
    }

    public function handle(string $key): JsonResponse
    {
        $setting = Setting::where('key', $key)->firstOrFail();

        Log::debug('Setting found successfully', ['setting' => $setting->key]);

        $setting->delete();

        Log::debug('Setting removed successfully', ['setting' => $setting->key]);

        return response()->json(['message' => 'Deleted']);
    }
}
