<?php

namespace App\Http\Requests\Billing;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OAT;

#[OAT\Post(
    path: '/api/subscribe',
    description: 'Generates a Stripe Checkout URL for the selected subscription plan.',
    summary: 'Create Stripe Checkout Session',
    security: [['sanctum' => []]],
    requestBody: new OAT\RequestBody(
        required: true,
        content: new OAT\JsonContent(
            required: ['price_id'],
            properties: [
                new OAT\Property(
                    property: 'price_id',
                    description: 'Stripe Price API ID',
                    type: 'string',
                    example: 'price_1TI47gDY7sR3maRKIOw3iWi0'
                )
            ]
        )
    ),
    tags: ['Billing'],
    responses: [
        new OAT\Response(
            response: 201,
            description: 'Checkout session created successfully. Returns the URL for payment.',
            content: new OAT\JsonContent(
                properties: [
                    new OAT\Property(
                        property: 'url',
                        type: 'string',
                        example: 'https://stripe.com...'
                    )
                ]
            )
        ),
        new OAT\Response(
            response: 422,
            description: 'Unprocessable Entity (Invalid or missing price_id)'
        ),
        new OAT\Response(
            response: 409,
            description: 'Conflict (User is already subscribed)'
        ),
        new OAT\Response(
            response: 401,
            description: 'Unauthenticated (Missing or invalid Sanctum token)'
        )
    ]
)]
class CheckoutRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'price_id' => [
                'required',
                'string',
                Rule::in(config('services.stripe.plans')),
            ],
        ];
    }
}
