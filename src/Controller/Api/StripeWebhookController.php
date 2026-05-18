<?php

/**
 * Stripe webhook entry-point — minimal proxy.
 *
 * The controller's responsibility is narrowed to:
 *  - verifying the Stripe signature (the only authentication available),
 *  - building a typed Stripe\Event from the payload,
 *  - handing it off to StripeEventDispatcher for routing.
 *
 * All domain logic — order creation, stock movement, refund accounting,
 * email notifications — lives in the dedicated services. Adding a new
 * event type requires zero changes here.
 *
 * Status policy:
 *  - 400 only for signature/payload errors — Stripe will surface this in
 *    the dashboard and stop retrying.
 *  - 200 for every other outcome so Stripe's exponential retry never
 *    hammers permanent business-level failures. The real reason is in
 *    the logs, never in the HTTP response.
 */

declare(strict_types=1);

namespace App\Controller\Api;

use App\Service\StripeEventDispatcher;
use Psr\Log\LoggerInterface;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StripeWebhookController extends AbstractController
{
    #[Route('/api/webhooks/stripe', name: 'api_stripe_webhook', methods: ['POST'])]
    public function handleWebhook(
        Request $request,
        StripeEventDispatcher $dispatcher,
        LoggerInterface $logger
    ): Response {
        $payload        = $request->getContent();
        $sigHeader      = (string) $request->headers->get('stripe-signature', '');
        $endpointSecret = (string) ($_ENV['STRIPE_WEBHOOK_SECRET'] ?? '');

        if ($endpointSecret === '') {
            $logger->error('[Stripe] STRIPE_WEBHOOK_SECRET is not set');
            return new Response('Configuration error', 500);
        }

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (\UnexpectedValueException $e) {
            $logger->error('[Stripe] Invalid payload', ['error' => $e->getMessage()]);
            return new Response('Invalid payload', 400);
        } catch (SignatureVerificationException $e) {
            $logger->error('[Stripe] Invalid signature', ['error' => $e->getMessage()]);
            return new Response('Invalid signature', 400);
        }

        try {
            $dispatcher->dispatch($event);
        } catch (\Throwable $e) {
            $logger->error('[Stripe] Dispatcher threw — swallowed to keep 200 for Stripe', [
                'event_type' => $event->type ?? null,
                'event_id'   => $event->id ?? null,
                'error'      => $e->getMessage(),
            ]);
        }

        return new Response('Success', 200);
    }
}
