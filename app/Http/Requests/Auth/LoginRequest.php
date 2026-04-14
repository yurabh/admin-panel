<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OAT;

#[OAT\Schema(
    schema: 'LoginRequest',
    required: ['email', 'password'],
    properties: [
        new OAT\Property(property: 'email', type: 'string', format: 'email', example: 'admin@example.com'),
        new OAT\Property(property: 'password', type: 'string', format: 'password', example: 'secret123'),
    ]
)]
class LoginRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }
}
