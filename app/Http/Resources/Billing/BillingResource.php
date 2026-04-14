<?php

namespace App\Http\Resources\Billing;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OAT;

#[OAT\Get(
    path: '/api/billing/info',
    description: 'Retrieves current subscription status, trial details, and linked payment method information.',
    summary: 'Get User Billing Information',
    security: [['sanctum' => []]],
    tags: ['Billing'],
    responses: [
        new OAT\Response(
            response: 200,
            description: 'Successful retrieval of billing information',
            content: new OAT\JsonContent(
                properties: [
                    new OAT\Property(property: 'id', type: 'integer', example: 1),
                    new OAT\Property(property: 'name', type: 'string', example: 'John Doe'),
                    new OAT\Property(property: 'email', type: 'string', example: 'john@example.com'),
                    new OAT\Property(
                        property: 'billing',
                        properties: [
                            new OAT\Property(property: 'is_active', type: 'boolean', example: true),
                            new OAT\Property(property: 'is_past_due', type: 'boolean', example: false),
                            new OAT\Property(property: 'on_trial', type: 'boolean', example: false),
                            new OAT\Property(property: 'ends_at', type: 'string', format: 'date-time', example: '2024-12-31 23:59:59', nullable: true),
                            new OAT\Property(property: 'card_brand', type: 'string', example: 'Visa'),
                            new OAT\Property(property: 'card_last_four', type: 'string', example: '4242'),
                            new OAT\Property(property: 'card_display', type: 'string', example: 'Visa •••• 4242'),
                        ],
                        type: 'object'
                    ),
                ]
            )
        ),
        new OAT\Response(response: 401, description: 'Unauthenticated'),
    ]
)]
class BillingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $subscription = $this->subscription('default');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'billing' => [
                'is_active' => $this->subscribed('default'),
                'is_past_due' => $subscription ? $subscription->pastDue() : false,
                'on_trial' => $this->onTrial('default'),
                'ends_at' => $subscription?->ends_at?->toDateTimeString(),
                'card_brand' => ucfirst($this->pm_type),
                'card_last_four' => $this->pm_last_four,
                'card_display' => $this->pm_type
                    ? ucfirst($this->pm_type).' •••• '.$this->pm_last_four
                    : 'No card linked',
            ],
        ];
    }
}
