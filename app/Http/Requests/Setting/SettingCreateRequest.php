<?php

namespace App\Http\Requests\Setting;

use Illuminate\Foundation\Http\FormRequest;

class SettingCreateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $settingId = $this->route('setting')?->id;
        return [
            'key' => [
                'required', 'string', 'max:255',
                'unique:settings,key,' . $settingId,
            ],
            'value' => 'required|string',
        ];
    }
}
