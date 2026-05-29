# AltheaSystemBackEnd
AltheaSystem backend

## Stripe webhook — local testing

The webhook endpoint is exposed at `POST /api/webhooks/stripe` and routes events through `StripeEventDispatcher` to the matching domain service:

| Stripe event                       | Handler                                  | Effect                                                    |
|------------------------------------|------------------------------------------|-----------------------------------------------------------|
| `checkout.session.completed`       | `OrderService::handleSessionCompleted`   | Creates the Order, decrements stock, sends confirmation.  |
| `checkout.session.expired`         | `OrderService::handleSessionExpired`     | Emails the user a "resume your order" link.               |
| `payment_intent.payment_failed`    | `OrderService::handlePaymentFailed`      | Marks the order `Echec paiement`, emails the user.        |
| `charge.refunded`                  | `RefundService::handleChargeRefunded`    | Restores stock, marks `Remboursée`, emails the user.      |

Any other event is logged at `info` and ignored.

### Stripe CLI

```bash
# 1. Forward live webhook traffic to your local server.
#    The command prints a `whsec_...` value — copy it into STRIPE_WEBHOOK_SECRET
#    in your .env.local, then restart Symfony so it picks it up.
stripe listen --forward-to localhost:8000/api/webhooks/stripe

# 2. In a second terminal, fire each event type:
stripe trigger checkout.session.completed
stripe trigger checkout.session.expired
stripe trigger payment_intent.payment_failed
stripe trigger charge.refunded
```

### What to expect in the logs

Every handler emits structured logs with `stripe_session_id`, `stripe_payment_intent_id`, and (when relevant) `order_id`. Idempotent no-ops are logged at `info` so re-delivered events are visible without being treated as errors. Stock mismatches at webhook time (price or quantity drift) log at `alert` and trigger an admin notification when `ADMIN_EMAIL` is configured.

### HTTP response policy

- `400` only for an invalid Stripe signature or malformed payload — Stripe stops retrying.
- `200` for everything else, including business-level failures. The reason is in the logs, never in the response.
