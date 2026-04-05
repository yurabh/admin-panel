<?php

namespace App\Services;

use App\Exceptions\BillingException;
use App\Models\User;
use Exception;
use Symfony\Component\HttpFoundation\Response;

class BillingService
{
    /**
     * @throws Exception
     */
    public function downloadInvoice(User $user, string $invoiceId): Response
    {
        $invoice = $user->findInvoice($invoiceId);

        if (!$invoice) {
            throw new BillingException("Invoice not found or access denied.", 404);
        }

        return $invoice->download([
            'vendor' => config('app.name'),
            'product' => 'Premium Subscription',
        ]);
    }
}
