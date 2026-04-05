<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Billing\DownloadInvoiceRequest;
use App\Http\Resources\Billing\BillingResource;
use App\Http\Resources\Billing\InvoiceResource;
use App\Services\BillingService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Attributes as OAT;

class BillingController extends Controller
{
    public function __construct(
        protected BillingService $billingService
    )
    {
    }

    #[OAT\Get(
        path: '/api/billing/info',
        description: 'Retrieves subscription status, trial details, and payment method info.',
        summary: 'Get user billing information',
        security: [['sanctum' => []]],
        tags: ['Billing'],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Successful retrieval of billing info',
                content: new OAT\JsonContent(ref: '#/components/schemas/BillingResource')
            ),
            new OAT\Response(response: 401, description: 'Unauthenticated')
        ]
    )]
    public function show(Request $request): BillingResource
    {
        return BillingResource::make($request->user());
    }

    #[OAT\Get(
        path: '/api/billing/invoices',
        description: 'Returns a history of all subscription payments.',
        summary: 'Get list of invoices',
        security: [['sanctum' => []]],
        tags: ['Billing'],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'List of invoices',
                content: new OAT\JsonContent(
                    type: 'array',
                    items: new OAT\Items(ref: '#/components/schemas/InvoiceResource')
                )
            ),
            new OAT\Response(response: 401, description: 'Unauthenticated')
        ]
    )]
    public function invoices(Request $request): AnonymousResourceCollection
    {
        $invoices = $request->user()->invoices();
        return InvoiceResource::collection($invoices);
    }

    #[OAT\Get(
        path: '/api/billing/invoices/download',
        description: 'Generates and downloads a PDF for a specific Stripe invoice.',
        summary: 'Download invoice PDF',
        security: [['sanctum' => []]],
        tags: ['Billing'],
        parameters: [
            new OAT\Parameter(
                name: 'invoice_id',
                description: 'The Stripe Invoice ID (e.g., in_123...)',
                in: 'query',
                required: true,
                schema: new OAT\Schema(type: 'string')
            )
        ],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Returns the PDF file',
                content: new OAT\MediaType(mediaType: 'application/pdf')
            ),
            new OAT\Response(response: 404, description: 'Invoice not found'),
            new OAT\Response(response: 401, description: 'Unauthenticated')
        ]
    )]
    public function downloadInvoice(DownloadInvoiceRequest $request): Response
    {
        $invoiceId = $request->validated('invoice_id');
        try {
            return $this->billingService->downloadInvoice($request->user(), $invoiceId);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }
}
