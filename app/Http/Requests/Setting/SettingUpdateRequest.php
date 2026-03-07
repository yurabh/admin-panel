<?php

namespace App\Http\Requests\Setting;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SettingUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $setting = $this->route('setting');
        $settingId = is_object($setting) ? $setting->id : $setting;

        return [
            'key' => [
                'required', 'string', 'max:255',
                Rule::unique('settings', 'key')->ignore($settingId),
            ],
            'value' => 'required|string',
        ];
    }
}
