<?php

namespace App\Http\Requests\Category;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OAT;

#[OAT\Schema(
    schema: 'CategoryRequest',
    description: 'Data for creating or updating a category',
    required: ['name', 'slug'],
    properties: [
        new OAT\Property(
            property: 'name',
            type: 'string',
            example: 'Technology',
            maxLength: 255
        ),
        new OAT\Property(
            property: 'slug',
            description: 'Unique slug for the category',
            type: 'string',
            example: 'technology'
        ),
    ],
    type: 'object'
)]
class CategoryRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => [
                'required', 'string',
                Rule::unique('categories', 'slug')->ignore($this->route('category')),
            ],
        ];
    }
}
