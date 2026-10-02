<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

/**
 * The answer to opening, changing or asking after one subscription: the
 * subscription as it now stands and who it is for. The customer is also
 * on the subscription itself, the way a listed one carries it.
 */
final readonly class SubscriptionDetails
{
    use ReadsFields;

    public function __construct(
        public Result $result,
        public Subscription $subscription,
        /** Who the subscription is for. */
        public ?NamedCustomer $customer,
    ) {}

    /**
     * @param  array<string, mixed>  $body
     */
    public static function fromArray(array $body): self
    {
        $customer = self::object($body['customer'] ?? null);

        return new self(
            result: Result::fromArray($body),
            subscription: Subscription::fromArray([...(self::object($body['subscription'] ?? null) ?? []), 'customer' => $customer]),
            customer: $customer === null ? null : NamedCustomer::fromArray($customer),
        );
    }
}
