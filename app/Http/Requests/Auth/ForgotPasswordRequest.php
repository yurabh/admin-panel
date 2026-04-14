<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OAT;

#[OAT\Schema(
    schema: 'ForgotPasswordRequest',
    required: ['email'],
    properties: [
        new OAT\Property(
            property: 'email',
            description: 'User email address to send the reset link to',
            type: 'string',
            format: 'email',
            example: 'admin@example.com'
        ),
    ]
)]
class ForgotPasswordRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
        ];
    }
}
