<?php

namespace App\Http\Requests\Tag;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OAT;

#[OAT\Schema(
    schema: 'TagRequest',
    required: ['name', 'slug'],
    properties: [
        new OAT\Property(property: 'name', type: 'string', example: 'Laravel', maxLength: 100),
        new OAT\Property(property: 'slug', description: 'Unique slug for the tag', type: 'string', example: 'laravel'),
        new OAT\Property(property: 'description', type: 'string', example: 'Framework related posts', nullable: true),
        new OAT\Property(property: 'is_active', type: 'boolean', example: true),
    ]
)]
class TagRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'slug' => 'required|string|unique:tags,slug,'.($this->tag?->id),
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ];
    }
}
