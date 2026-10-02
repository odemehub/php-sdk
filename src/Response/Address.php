<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

/**
 * An address as the gateway answers it: only the fields that were said.
 * A payment's billing address carries the person fields alone; an order's
 * or a subscription's carries the company fields too, when the customer
 * buys for one.
 */
final readonly class Address
{
    use ReadsFields;

    public function __construct(
        public ?string $firstname,
        public ?string $lastname,
        public ?string $email,
        public ?string $phone,
        public ?string $address,
        public ?string $district,
        public ?string $province,
        public ?string $country,
        public ?string $companyTitle,
        public ?string $taxNumber,
        public ?string $taxOffice,
    ) {}

    /**
     * @param  array<string, mixed>  $address
     */
    public static function fromArray(array $address): self
    {
        return new self(
            firstname: self::said($address['firstname'] ?? null),
            lastname: self::said($address['lastname'] ?? null),
            email: self::said($address['email'] ?? null),
            phone: self::said($address['phone'] ?? null),
            address: self::said($address['address'] ?? null),
            district: self::said($address['district'] ?? null),
            province: self::said($address['province'] ?? null),
            country: self::said($address['country'] ?? null),
            companyTitle: self::said($address['company_title'] ?? null),
            taxNumber: self::said($address['tax_number'] ?? null),
            taxOffice: self::said($address['tax_office'] ?? null),
        );
    }
}
