<?php

namespace App\Http\Requests\Page;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OAT;

#[OAT\Schema(
    schema: 'StorePageRequest',
    required: ['title', 'slug', 'content'],
    properties: [
        new OAT\Property(property: 'title', type: 'string', example: 'About Our Company', maxLength: 255),
        new OAT\Property(property: 'slug', description: 'Must be unique in pages table', type: 'string', example: 'about-our-company', maxLength: 255),
        new OAT\Property(property: 'content', type: 'string', example: 'Detailed information about the company...'),
        new OAT\Property(property: 'user_id', description: 'ID of the user who owns the page', type: 'integer', example: 1),
        new OAT\Property(property: 'is_published', type: 'boolean', example: false),
        new OAT\Property(
            property: 'image',
            description: 'Featured image file (max 2MB)',
            type: 'string',
            format: 'binary',
            nullable: true
        ),
    ]
)]
class StorePageRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:pages,slug'],
            'content' => ['required', 'string'],
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'is_published' => ['boolean'],
            'image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'user_id' => $this->user()?->id,
        ]);
    }
}
