<?php

namespace App\Http\Resources\Billing;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OAT;

#[OAT\Get(
    path: '/api/billing/invoices',
    description: 'Retrieves a history of all subscription payments and their statuses.',
    summary: 'Get List of Invoices',
    security: [['sanctum' => []]],
    tags: ['Billing'],
    responses: [
        new OAT\Response(
            response: 200,
            description: 'Successful retrieval of invoices list',
            content: new OAT\JsonContent(
                type: 'array',
                items: new OAT\Items(
                    properties: [
                        new OAT\Property(property: 'id', type: 'string', example: 'in_1TI47gDY7sR3maRKIOw3iWi0'),
                        new OAT\Property(property: 'amount', type: 'string', example: '$10.00'),
                        new OAT\Property(property: 'date', type: 'string', example: 'Dec 31, 2024'),
                        new OAT\Property(property: 'status', type: 'string', example: 'paid'),
                        new OAT\Property(
                            property: 'pdf_url',
                            type: 'string',
                            example: 'http://localhost/api/billing/invoices/download?invoice_id=in_123'
                        )
                    ]
                )
            )
        ),
        new OAT\Response(response: 401, description: 'Unauthenticated')
    ]
)]
class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount' => $this->total(),
            'date' => $this->date()->toFormattedDateString(),
            'status' => $this->status,
            'pdf_url' => route('api.invoices.download', ['invoice_id' => $this->id]),
        ];
    }
}
