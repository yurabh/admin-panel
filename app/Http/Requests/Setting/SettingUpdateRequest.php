<?php

namespace App\Http\Requests\Setting;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OAT;

#[OAT\Schema(
    schema: 'SettingUpdateRequest',
    required: ['key', 'value'],
    properties: [
        new OAT\Property(
            property: 'key',
            description: 'Unique key for the setting (ignores current record ID)',
            type: 'string',
            example: 'site_logo',
            maxLength: 255
        ),
        new OAT\Property(
            property: 'value',
            description: 'The updated value for this setting',
            type: 'string',
            example: '/images/logo.png'
        ),
    ]
)]
class SettingUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
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
