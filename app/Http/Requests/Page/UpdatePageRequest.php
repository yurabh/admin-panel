<?php

namespace App\Http\Requests\Page;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OAT;

#[OAT\Schema(
    schema: 'UpdatePageRequest',
    description: 'Request body for updating an existing page. All fields are optional.',
    properties: [
        new OAT\Property(property: 'title', type: 'string', example: 'Updated Page Title', maxLength: 255),
        new OAT\Property(property: 'slug', description: 'Must be unique if provided', type: 'string', example: 'updated-page-slug', maxLength: 255),
        new OAT\Property(property: 'content', type: 'string', example: 'New content for the page...'),
        new OAT\Property(property: 'is_published', type: 'boolean', example: true),
        new OAT\Property(property: 'user_id', description: 'Assign to another user ID', type: 'integer', example: 1),
        new OAT\Property(
            property: 'image',
            description: 'Optional new image file (max 2MB)',
            type: 'string',
            format: 'binary',
            nullable: true
        ),
    ]
)]
class UpdatePageRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255', 'unique:pages,slug'],
            'content' => ['sometimes', 'string'],
            'is_published' => ['sometimes', 'boolean'],
            'image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'user_id' => ['sometimes', 'integer', 'exists:users,id'],
        ];
    }
}
