<?php

namespace App\Http\Controllers\Setting;

use App\Actions\Setting\SettingCreateAction;
use App\Actions\Setting\SettingDeleteAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Setting\SettingCreateRequest;
use App\Http\Requests\Setting\SettingUpdateRequest;
use App\Http\Resources\Setting\SettingResource;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $allCached = Setting::getAllCached();

        return response()->json(
            $allCached
        );
    }


    /**
     * Store a newly created resource in storage.
     * @throws \Throwable
     */
    public function store(SettingCreateRequest $request, SettingCreateAction $action)
    {
        $setting = DB::transaction(fn() => $action->handle($request));

        Log::debug('Setting created', ['setting' => $setting->id]);

        return SettingResource::make($setting);
    }


    /**
     * Display the specified resource.
     */
    public function show(string $key)
    {
        $value = Setting::getValue($key);

        return $value ? response()->json(['key' => $key, 'value' => $value])
            : response()->json(['message' => 'Not found'], 404);
    }


    /**
     * Update the specified resource in storage.
     * @throws \Throwable
     */
    public function update(SettingUpdateRequest $request, Setting $setting)
    {
        $data = $request->validated();

        Log::debug('Validation passed', ['id' => $setting->id]);

        DB::transaction(fn() => $setting->update($data));

        Log::debug('Setting updated', ['setting' => $setting->id]);

        return SettingResource::make($setting);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $key, SettingDeleteAction $action)
    {
        return $action->handle($key);
    }
}
