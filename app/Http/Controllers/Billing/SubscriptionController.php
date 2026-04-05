<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Billing\CheckoutRequest;
use App\Services\SubscriptionService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OAT;

class SubscriptionController extends Controller
{
    public function __construct(
        protected SubscriptionService $subscriptionService
    )
    {
    }

    #[OAT\Post(
        path: '/api/subscribe',
        description: 'Initiates a Stripe Checkout session for a specific price ID and returns the redirect URL.',
        summary: 'Create Checkout Session',
        security: [['sanctum' => []]],
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(
                required: ['price_id'],
                properties: [
                    new OAT\Property(
                        property: 'price_id',
                        description: 'The Stripe Price ID from your dashboard',
                        type: 'string',
                        example: 'price_1TI47gDY7sR3maRKIOw3iWi0'
                    )
                ]
            )
        ),
        tags: ['Subscription'],
        responses: [
            new OAT\Response(
                response: 201,
                description: 'Checkout session created successfully',
                content: new OAT\JsonContent(
                    properties: [
                        new OAT\Property(property: 'url', type: 'string', example: 'https://stripe.com...')
                    ]
                )
            ),
            new OAT\Response(
                response: 409,
                description: 'Conflict: User is already subscribed to this plan',
                content: new OAT\JsonContent(
                    properties: [
                        new OAT\Property(property: 'message', type: 'string', example: 'You are already subscribed to this plan.')
                    ]
                )
            ),
            new OAT\Response(
                response: 422,
                description: 'Unprocessable Entity: Validation failed (invalid price_id)',
                content: new OAT\JsonContent(
                    properties: [
                        new OAT\Property(property: 'message', type: 'string', example: 'The selected price_id is invalid.')
                    ]
                )
            ),
            new OAT\Response(
                response: 401,
                description: 'Unauthenticated: Missing or invalid token'
            ),
            new OAT\Response(
                response: 500,
                description: 'Internal Server Error: Payment service unavailable'
            )
        ]
    )]
    public function checkout(CheckoutRequest $request): JsonResponse
    {
        try {
            $url = $this->subscriptionService->createCheckoutSession(
                $request->user(),
                $request->validated('price_id'));
            return response()->json(['url' => $url], 201);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    #[OAT\Post(
        path: '/api/subscription/cancel',
        description: 'Cancels the current active subscription. The user will maintain access until the end of the billing period (Grace Period).',
        summary: 'Cancel active subscription',
        security: [['sanctum' => []]],
        tags: ['Subscription'],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Subscription cancelled successfully',
                content: new OAT\JsonContent(
                    properties: [
                        new OAT\Property(property: 'status', type: 'string', example: 'success'),
                        new OAT\Property(
                            property: 'data',
                            properties: [
                                new OAT\Property(property: 'status', type: 'string', example: 'cancelled'),
                                new OAT\Property(property: 'ends_at', type: 'string', example: '2024-12-31 23:59:59'),
                                new OAT\Property(property: 'message', type: 'string', example: 'Subscription cancelled. Access remains active until the end of the period.')
                            ],
                            type: 'object'
                        )
                    ]
                )
            ),
            new OAT\Response(
                response: 404,
                description: 'Not Found: No active subscription found for this user',
                content: new OAT\JsonContent(
                    properties: [
                        new OAT\Property(property: 'message', type: 'string', example: 'No active subscription found.')
                    ]
                )
            ),
            new OAT\Response(
                response: 401,
                description: 'Unauthenticated: Missing or invalid token'
            )
        ]
    )]
    public function cancel(Request $request): JsonResponse
    {
        try {
            $data = $this->subscriptionService->cancelSubscription($request->user());
            return response()->json([
                'status' => 'success',
                'data' => $data
            ]);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    #[OAT\Get(
        path: '/api/subscription/portal',
        description: 'Generates a secure link to the Stripe customer portal where users can manage payment methods and billing details.',
        summary: 'Get Stripe Billing Portal URL',
        security: [['sanctum' => []]],
        tags: ['Subscription'],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Billing portal URL generated successfully',
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
                response: 404,
                description: 'Not Found: User does not have a Stripe billing history yet',
                content: new OAT\JsonContent(
                    properties: [
                        new OAT\Property(property: 'message', type: 'string', example: 'You do not have a billing history yet.')
                    ]
                )
            ),
            new OAT\Response(
                response: 401,
                description: 'Unauthenticated: Missing or invalid token'
            )
        ]
    )]
    public function portal(Request $request): JsonResponse
    {
        try {
            $url = $this->subscriptionService->getBillingPortalUrl($request->user());
            return response()->json([
                'url' => $url
            ]);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    #[OAT\Get(
        path: '/api/subscription/success',
        description: 'Processes the checkout session completion after the user is redirected back from Stripe. Updates payment methods and verifies subscription status.',
        summary: 'Handle Successful Payment Redirect',
        tags: ['Subscription'],
        parameters: [
            new OAT\Parameter(
                name: 'session_id',
                description: 'The Stripe Checkout Session ID provided in the redirect URL',
                in: 'query',
                required: true,
                schema: new OAT\Schema(type: 'string'),
                example: 'cs_test_a1b2c3d4...'
            )
        ],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Payment successfully verified and processed',
                content: new OAT\JsonContent(
                    properties: [
                        new OAT\Property(property: 'status', type: 'string', example: 'success'),
                        new OAT\Property(property: 'redirect_url', type: 'string', example: '/dashboard'),
                        new OAT\Property(property: 'message', type: 'string', example: 'Payment successful')
                    ]
                )
            ),
            new OAT\Response(
                response: 400,
                description: 'Bad Request: Missing or invalid Session ID'
            ),
            new OAT\Response(
                response: 404,
                description: 'Not Found: Billable user not found for this session'
            ),
            new OAT\Response(
                response: 500,
                description: 'Internal Server Error: Failed to retrieve session data from Stripe'
            )
        ]
    )]
    public function success(Request $request): JsonResponse
    {
        $sessionId = $request->query('session_id');
        if (!$sessionId) {
            return response()->json(['error' => 'No session ID'], 400);
        }
        try {
            $user = $this->subscriptionService->handleSuccessfulPayment($sessionId);
            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }
            return response()->json([
                'status' => 'success',
                'redirect_url' => '/dashboard',
                'message' => 'Payment successful'
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    #[OAT\Post(
        path: '/api/subscription/start-trial',
        description: 'Activates a 7-day free trial for the selected plan without requiring a credit card upfront.',
        summary: 'Start Free Trial',
        security: [['sanctum' => []]],
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(
                required: ['price_id'],
                properties: [
                    new OAT\Property(
                        property: 'price_id',
                        description: 'The Stripe Price ID for the trial plan',
                        type: 'string',
                        example: 'price_1TI47gDY7sR3maRKIOw3iWi0'
                    )
                ]
            )
        ),
        tags: ['Subscription'],
        responses: [
            new OAT\Response(
                response: 201,
                description: 'Trial period activated successfully',
                content: new OAT\JsonContent(
                    properties: [
                        new OAT\Property(property: 'status', type: 'string', example: 'success'),
                        new OAT\Property(property: 'message', type: 'string', example: 'Trial started! Enjoy your 7 days of premium access.')
                    ]
                )
            ),
            new OAT\Response(
                response: 409,
                description: 'Conflict: User has already used their trial period',
                content: new OAT\JsonContent(
                    properties: [
                        new OAT\Property(property: 'message', type: 'string', example: 'You have already used your trial period.')
                    ]
                )
            ),
            new OAT\Response(
                response: 422,
                description: 'Unprocessable Entity: Invalid price_id provided'
            ),
            new OAT\Response(
                response: 401,
                description: 'Unauthenticated: Missing or invalid token'
            )
        ]
    )]
    public function startTrial(CheckoutRequest $request): JsonResponse
    {
        try {
            $this->subscriptionService->startFreeTrialWithoutCard(
                $request->user(),
                $request->validated('price_id'));
            return response()->json([
                'status' => 'success',
                'message' => 'Trial started! Enjoy your 5 days of premium access.'
            ], 201);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }
}
