<?php

namespace App\Http\Requests\Comment;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OAT;

#[OAT\Schema(
    schema: 'CommentRequest',
    description: 'Data for creating or updating a comment',
    required: ['post_id', 'content'],
    properties: [
        new OAT\Property(property: 'post_id', type: 'integer', example: 10),
        new OAT\Property(property: 'user_id', type: 'integer', example: 1),
        new OAT\Property(property: 'content', type: 'string', example: 'This is a great post!', maxLength: 2000),
        new OAT\Property(property: 'is_approved', type: 'boolean', example: true, default: false),
    ],
    type: 'object'
)]
class CommentRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'post_id' => 'required|integer|exists:posts,id',
            'user_id' => 'required|integer|exists:users,id',
            'content' => 'required|string|max:2000',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'user_id' => $this->user()?->id,
        ]);
    }
}
