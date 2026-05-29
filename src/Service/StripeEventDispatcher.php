<?php

/**
 * Routes verified Stripe events to the right domain service.
 *
 * The webhook controller's job ends at signature verification; from there
 * this dispatcher decides which handler to run. Adding a new event type is
 * a one-case-statement change here, plus the handler on the relevant
 * service — the controller never needs to be touched.
 *
 * Every handler returns void and is expected to:
 *  - swallow domain-level errors (the response to Stripe is always 200 once
 *    the signature passed; retries on permanent failures would just spam
 *    the system),
 *  - guarantee its own idempotency,
 *  - log enough context (stripe_session_id / payment_intent_id / order_id)
 *    to be debuggable from logs alone.
 */

declare(strict_types=1);

namespace App\Service;

use Psr\Log\LoggerInterface;
use Stripe\Event as StripeEvent;

final class StripeEventDispatcher
{
    public function __construct(
        private readonly OrderService $orderService,
        private readonly RefundService $refundService,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function dispatch(StripeEvent $event): void
    {
        $object    = $event->data->object ?? null;
        $eventType = (string) $event->type;

        $this->logger->info('[Stripe] Dispatching event', [
            'event_type' => $eventType,
            'event_id'   => $event->id ?? null,
            'object_id'  => is_object($object) ? ($object->id ?? null) : null,
        ]);

        switch ($eventType) {
            case 'checkout.session.completed':
                if ($object instanceof \Stripe\Checkout\Session) {
                    $this->orderService->handleSessionCompleted($object);
                }
                break;

            case 'checkout.session.expired':
                if ($object instanceof \Stripe\Checkout\Session) {
                    $this->orderService->handleSessionExpired($object);
                }
                break;

            case 'payment_intent.payment_failed':
                if ($object instanceof \Stripe\PaymentIntent) {
                    $this->orderService->handlePaymentFailed($object);
                }
                break;

            case 'charge.refunded':
                if ($object instanceof \Stripe\Charge) {
                    $this->refundService->handleChargeRefunded($object);
                }
                break;

            default:
                $this->logger->info('[Stripe] Event ignored — no handler registered', [
                    'event_type' => $eventType,
                ]);
        }
    }
}
