<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use OpenApi\Attributes as OAT;

#[OAT\Schema(
    schema: 'ResetPasswordRequest',
    required: ['token', 'email', 'password', 'password_confirmation'],
    properties: [
        new OAT\Property(property: 'token', description: 'The password reset token received via email', type: 'string', example: 'abcdef123456...'),
        new OAT\Property(property: 'email', type: 'string', format: 'email', example: 'user@example.com'),
        new OAT\Property(property: 'password', type: 'string', format: 'password', example: 'Password123!', minLength: 8),
        new OAT\Property(property: 'password_confirmation', type: 'string', format: 'password', example: 'Password123!'),
    ]
)]
class ResetPasswordRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'token' => ['required', 'string'],
            'email' => ['required', 'email', 'exists:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()->symbols(),
            ],
        ];
    }
}
