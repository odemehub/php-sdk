<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

/**
 * Where a page sits in an answer that comes a page at a time.
 */
final readonly class Pagination
{
    use ReadsFields;

    public function __construct(
        /** The page answered, from 1. */
        public int $page,
        /** How many records a page holds. */
        public int $perPage,
        /** How many records there are on every page together. */
        public int $total,
        /** The last page there is; 1 when there is nothing at all. */
        public int $lastPage,
    ) {}

    /**
     * Whether there is a page after this one.
     */
    public function hasMorePages(): bool
    {
        return $this->page < $this->lastPage;
    }

    /**
     * @param  array<string, mixed>  $pagination
     */
    public static function fromArray(array $pagination): self
    {
        return new self(
            page: self::count($pagination['page'] ?? null) ?? 1,
            perPage: self::count($pagination['per_page'] ?? null) ?? 0,
            total: self::count($pagination['total'] ?? null) ?? 0,
            lastPage: self::count($pagination['last_page'] ?? null) ?? 1,
        );
    }
}
