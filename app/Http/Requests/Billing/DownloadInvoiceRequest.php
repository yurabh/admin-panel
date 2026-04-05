<?php

namespace App\Http\Requests\Billing;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OAT;

#[OAT\Get(
    path: '/api/billing/invoices/download',
    description: 'Generates and downloads a PDF version of the specified Stripe invoice.',
    summary: 'Download Invoice PDF',
    security: [['sanctum' => []]],
    tags: ['Billing'],
    parameters: [
        new OAT\Parameter(
            name: 'invoice_id',
            description: 'The unique Stripe Invoice ID (starts with in_)',
            in: 'query',
            required: true,
            schema: new OAT\Schema(type: 'string'),
            example: 'in_1TI47gDY7sR3maRKIOw3iWi0'
        )
    ],
    responses: [
        new OAT\Response(
            response: 200,
            description: 'Returns the PDF file',
            content: new OAT\MediaType(
                mediaType: 'application/pdf'
            )
        ),
        new OAT\Response(
            response: 404,
            description: 'Invoice not found or access denied'
        ),
        new OAT\Response(
            response: 401,
            description: 'Unauthenticated'
        )
    ]
)]
class DownloadInvoiceRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'invoice_id' => 'required|string',
        ];
    }
}
