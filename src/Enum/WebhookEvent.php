<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Enum;

/**
 * What a webhook says happened. The first part of the name is what it is
 * about — `order`, `payment_link`, `subscription`, `transaction` — and the
 * webhook carries that thing's token. It is a notification, not the
 * answer: ask the gateway what became of the thing before acting on it.
 */
enum WebhookEvent: string
{
    case OrderPaid = 'order.paid';
    case OrderPaymentRefunded = 'order.payment_refunded';
    case OrderPaymentCancelled = 'order.payment_cancelled';
    case PaymentLinkPaid = 'payment_link.paid';
    case PaymentLinkPaymentRefunded = 'payment_link.payment_refunded';
    case PaymentLinkPaymentCancelled = 'payment_link.payment_cancelled';
    case SubscriptionActive = 'subscription.active';
    case SubscriptionPastDue = 'subscription.past_due';
    case SubscriptionCancelled = 'subscription.cancelled';
    case SubscriptionEnded = 'subscription.ended';
    case SubscriptionCompleted = 'subscription.completed';
    case SubscriptionPaymentRefunded = 'subscription.payment_refunded';
    case SubscriptionPaymentCancelled = 'subscription.payment_cancelled';
    case TransactionSuccessful = 'transaction.successful';
    case TransactionFailed = 'transaction.failed';
    case TransactionExpired = 'transaction.expired';
    case TransactionPaymentRefunded = 'transaction.payment_refunded';
    case TransactionPaymentCancelled = 'transaction.payment_cancelled';
}
