<?php

namespace App\Http\Resources\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OAT;

#[OAT\Schema(
    schema: 'LoginResource',
    description: 'Success login response with access token',
    properties: [
        new OAT\Property(property: 'access_token', type: 'string', example: '1|abcde12345...'),
        new OAT\Property(property: 'token_type', type: 'string', example: 'Bearer'),
        new OAT\Property(
            property: 'user',
            properties: [
                new OAT\Property(property: 'id', type: 'integer', example: 1),
                new OAT\Property(property: 'name', type: 'string', example: 'Yuriy'),
                new OAT\Property(property: 'email', type: 'string', example: 'yuriy@example.com'),
                new OAT\Property(property: 'role', ref: '#/components/schemas/UserRole'),
            ],
            type: 'object'
        ),
    ],
    type: 'object'
)]
class LoginResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'access_token' => $this->resource['token'],
            'token_type' => 'Bearer',
            'user' => [
                'id' => $this->resource['user']->id,
                'name' => $this->resource['user']->name,
                'email' => $this->resource['user']->email,
                'role' => $this->resource['user']->role,
            ],
        ];
    }
}
