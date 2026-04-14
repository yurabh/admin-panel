<?php

namespace App\Services;

use App\Exceptions\BillingException;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Cashier;
use Stripe\Checkout\Session as StripeCheckoutSession;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;

class SubscriptionService
{
    /**
     * @throws Exception
     */
    public function createCheckoutSession(User $user, string $priceId): string
    {
        if ($user->subscribed(User::SUBSCRIPTION_NAME)) {
            throw new BillingException('You are already subscribed to this plan.', 409);
        }
        try {
            $checkoutSession = $user->newSubscription(User::SUBSCRIPTION_NAME, $priceId)
                ->checkout([
                    'success_url' => config('app.frontend_url').'/api/subscription/success?session_id={CHECKOUT_SESSION_ID}',
                    'cancel_url' => config('app.frontend_url').'/cancel',
                    'payment_method_collection' => 'always',
                ]);

            return $checkoutSession->url;
        } catch (Exception $e) {
            Log::error('Stripe Checkout Error: '.$e->getMessage());
            throw new BillingException('Payment service unavailable. Please try again later.', 503);
        }
    }

    /**
     * @throws Exception
     */
    public function cancelSubscription(User $user): array
    {
        $subscription = $user->subscription(User::SUBSCRIPTION_NAME);

        if (! $subscription || ! $subscription->active()) {
            throw new BillingException('No active subscription found.', 404);
        }
        $subscription->cancel();

        return [
            'status' => 'cancelled',
            'ends_at' => $subscription->ends_at?->format('Y-m-d H:i:s'),
            'message' => 'Subscription cancelled. Access remains active until the end of the period.',
        ];
    }

    /**
     * @throws Exception
     */
    public function getBillingPortalUrl(User $user): string
    {
        if (! $user->hasStripeId()) {
            throw new BillingException('You do not have a billing history yet.', 404);
        }

        return $user->redirectToBillingPortal(url('/dashboard'))->getTargetUrl();
    }

    /**
     * @throws ApiErrorException
     */
    public function handleSuccessfulPayment(string $sessionId): ?User
    {
        Stripe::setApiKey(config('cashier.secret'));
        $session = StripeCheckoutSession::retrieve([
            'id' => $sessionId,
            'expand' => ['subscription.default_payment_method'],
        ]);
        $user = Cashier::findBillable($session->customer);
        if (! $user) {
            return null;
        }
        if ($session->subscription && $session->subscription->default_payment_method) {
            $user->updateDefaultPaymentMethod($session->subscription->default_payment_method->id);
        }

        return $user->refresh();
    }

    /**
     * @throws Exception
     */
    public function startFreeTrialWithoutCard(User $user, string $priceId): void
    {
        if ($user->subscriptions()->exists()) {
            throw new BillingException('You have already used your trial period.', 409);
        }
        try {
            $user->newSubscription(User::SUBSCRIPTION_NAME, $priceId)
                ->trialDays(5)
                ->create(null);
        } catch (Exception $e) {
            Log::error('Free Trial Activation Error: '.$e->getMessage());
            throw new BillingException('Could not start free trial.', 503);
        }
    }
}
