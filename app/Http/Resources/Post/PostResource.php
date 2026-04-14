<?php

namespace App\Http\Resources\Post;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OAT;

#[OAT\Schema(
    schema: 'PostResources',
    description: 'Detailed post resource with relations',
    properties: [
        new OAT\Property(property: 'id', type: 'integer', example: 1),
        new OAT\Property(property: 'title', type: 'string', example: 'Example Post Title'),
        new OAT\Property(property: 'slug', type: 'string', example: 'example-post-slug'),
        new OAT\Property(property: 'content', type: 'string', example: 'Full article content...'),
        new OAT\Property(property: 'is_published', type: 'boolean', example: true),
        new OAT\Property(property: 'published_at', type: 'string', format: 'date-time', nullable: true),
        new OAT\Property(
            property: 'category',
            properties: [
                new OAT\Property(property: 'id', type: 'integer', example: 5),
                new OAT\Property(property: 'name', type: 'string', example: 'Technology'),
                new OAT\Property(property: 'slug', type: 'string', example: 'technology'),
            ],
            type: 'object',
            nullable: true
        ),
        new OAT\Property(
            property: 'tags',
            type: 'array',
            items: new OAT\Items(
                properties: [
                    new OAT\Property(property: 'id', type: 'integer', example: 10),
                    new OAT\Property(property: 'name', type: 'string', example: 'Laravel'),
                    new OAT\Property(property: 'slug', type: 'string', example: 'laravel'),
                ],
                type: 'object'
            )
        ),
        new OAT\Property(
            property: 'author',
            properties: [
                new OAT\Property(property: 'id', type: 'integer', example: 1),
                new OAT\Property(property: 'name', type: 'string', example: 'Yuriy'),
            ],
            type: 'object',
            nullable: true
        ),
        new OAT\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OAT\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ],
    type: 'object'
)]
/**
 * @property mixed $created_at
 * @property mixed $updated_at
 * @property mixed $user
 * @property mixed $title
 * @property mixed $slug
 * @property mixed $content
 * @property mixed $tags
 */
class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->content,
            'is_published' => $this->is_published,
            'published_at' => $this->published_at,
            'category' => $this->category ? [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ] : null,
            'tags' => $this->tags->map(fn ($tag) => [
                'id' => $tag->id,
                'name' => $tag->name,
                'slug' => $tag->slug,
            ]),
            'author' => $this->user ? [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ] : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
