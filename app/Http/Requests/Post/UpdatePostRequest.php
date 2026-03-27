<?php

namespace App\Http\Requests\Post;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OAT;

#[OAT\Schema(
    schema: 'UpdatePostRequest',
    description: 'Data for updating an existing post',
    required: ['title', 'content', 'slug'],
    properties: [
        new OAT\Property(property: 'title', type: 'string', example: 'Updated Post Title', maxLength: 255),
        new OAT\Property(property: 'content', type: 'string', example: 'Updated content of the post.', maxLength: 2000),
        new OAT\Property(property: 'category_id', type: 'integer', example: 2, nullable: true),
        new OAT\Property(property: 'slug', type: 'string', example: 'updated-post-slug', maxLength: 255),
        new OAT\Property(property: 'user_id', type: 'integer', example: 1, nullable: true),
        new OAT\Property(
            property: 'tags',
            type: 'array',
            items: new OAT\Items(type: 'integer'),
            example: [1, 3, 5]
        ),
        new OAT\Property(property: 'is_published', type: 'boolean', example: true),
    ]
)]
class UpdatePostRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:2000',
            'category_id' => 'nullable|integer|max:255',
            'slug' => 'required|string|max:255|unique:posts,slug',
            'user_id' => 'nullable|integer|max:255',
            'tags' => 'array',
            'is_published' => 'boolean',
        ];
    }
}
