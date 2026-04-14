<?php

namespace App\Http\Resources\Comment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OAT;

#[OAT\Schema(
    schema: 'CommentResource',
    description: 'Comment resource with user and post details',
    properties: [
        new OAT\Property(property: 'id', type: 'integer', example: 1),
        new OAT\Property(property: 'content', type: 'string', example: 'This is a great post!'),
        new OAT\Property(property: 'is_approved', type: 'boolean', example: true),
        new OAT\Property(
            property: 'user',
            properties: [
                new OAT\Property(property: 'id', type: 'integer', example: 1),
                new OAT\Property(property: 'name', type: 'string', example: 'John Doe'),
                new OAT\Property(property: 'role', type: 'string', example: 'admin'),
            ],
            type: 'object'
        ),
        new OAT\Property(
            property: 'post',
            properties: [
                new OAT\Property(property: 'post_id', type: 'integer', example: 10),
                new OAT\Property(property: 'post_title', type: 'string', example: 'How to use Swagger'),
                new OAT\Property(property: 'created_at', type: 'string', example: '16.03.2024 14:30'),
            ],
            type: 'object'
        ),
    ],
    type: 'object'
)]
class CommentResource extends JsonResource
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
            'content' => $this->content,
            'is_approved' => (bool) $this->is_approved,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'role' => $this->user->role->value,
            ],
            'post' => [
                'post_id' => $this->post->id,
                'post_title' => $this->post->title,
                'created_at' => $this->created_at->format('d.m.Y H:i'),
            ],
        ];
    }
}
