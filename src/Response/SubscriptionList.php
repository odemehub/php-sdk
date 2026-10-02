<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

/**
 * Every subscription opened on a channel within a span of days, oldest
 * first, each with its customer on `Subscription::$customer`. The days
 * answered are the ones the gateway used: the ones asked for, or the last
 * seven when none were.
 */
final readonly class SubscriptionList
{
    use ReadsFields;

    /**
     * @param  list<Subscription>  $subscriptions
     */
    public function __construct(
        public Result $result,
        /** The first day listed, as `YYYY-MM-DD` in the team's timezone. */
        public string $createdFrom,
        /** The last day listed, the same way. */
        public string $createdTo,
        public array $subscriptions,
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
            subscriptions: self::each($body['subscriptions'] ?? null, Subscription::fromArray(...)),
        );
    }
}
