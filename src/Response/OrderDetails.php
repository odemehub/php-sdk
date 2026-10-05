<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

/**
 * The answer to opening or changing an order: the order as
 * it now stands and who it is for, as far as anybody has said. The
 * customer is also on the order itself, the way a listed one carries it.
 */
final readonly class OrderDetails
{
    use ReadsFields;

    public function __construct(
        public Result $result,
        public Order $order,
        /** Who the order is for; null while nobody has said. */
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
            order: Order::fromArray([...(self::object($body['order'] ?? null) ?? []), 'customer' => $customer]),
            customer: $customer === null ? null : NamedCustomer::fromArray($customer),
        );
    }
}
