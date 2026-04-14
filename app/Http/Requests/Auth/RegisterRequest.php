<?php

namespace App\Http\Requests\Auth;

use App\Enums\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Password;
use OpenApi\Attributes as OAT;

#[OAT\Schema(
    schema: 'RegisterRequest',
    required: ['name', 'email', 'password', 'password_confirmation'],
    properties: [
        new OAT\Property(property: 'name', type: 'string', example: 'Yuriy', maxLength: 255),
        new OAT\Property(property: 'email', type: 'string', format: 'email', example: 'yuriy@example.com', maxLength: 255),
        new OAT\Property(property: 'password', type: 'string', format: 'password', example: 'secret123'),
        new OAT\Property(property: 'password_confirmation', type: 'string', format: 'password', example: 'secret123'),
        new OAT\Property(property: 'role', ref: '#/components/schemas/UserRole', nullable: true),
    ]
)]
class RegisterRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['nullable', new Enum(UserRole::class)],
        ];
    }
}
