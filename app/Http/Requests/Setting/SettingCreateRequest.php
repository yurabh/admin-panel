<?php

namespace App\Http\Requests\Setting;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OAT;

#[OAT\Schema(
    schema: 'SettingCreateRequest',
    required: ['key', 'value'],
    properties: [
        new OAT\Property(
            property: 'key',
            description: 'Unique configuration key',
            type: 'string',
            example: 'site_name',
            maxLength: 255
        ),
        new OAT\Property(
            property: 'value',
            description: 'Value for the configuration key',
            type: 'string',
            example: 'My Admin Panel'
        ),
    ]
)]
class SettingCreateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $settingId = $this->route('setting')?->id;

        return [
            'key' => [
                'required', 'string', 'max:255',
                'unique:settings,key,'.$settingId,
            ],
            'value' => 'required|string',
        ];
    }
}
