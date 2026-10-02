<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

/**
 * Every order opened on a channel within a span of days, oldest first,
 * each with its customer on `Order::$customer`. The days answered are the
 * ones the gateway used: the ones asked for, or the last seven when none
 * were.
 */
final readonly class OrderList
{
    use ReadsFields;

    /**
     * @param  list<Order>  $orders
     */
    public function __construct(
        public Result $result,
        /** The first day listed, as `YYYY-MM-DD` in the team's timezone. */
        public string $createdFrom,
        /** The last day listed, the same way. */
        public string $createdTo,
        public array $orders,
    ) {}

    /**
     * @param  array<string, mixed>  $body
     */
    public static function fromArray(array $body): self
    {
        return new self(
            result: Result::fromArray($body),
            createdFrom: self::text($body['created_from'] ?? null),
            createdTo: self::text($body['created_to'] ?? null),
            orders: self::each($body['orders'] ?? null, Order::fromArray(...)),
        );
    }
}
