<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * Where a customer is billed, or where their goods go. A payment and a
 * kept card need the whole of the eight person fields on the billing
 * address; an order or a subscription takes whatever is known and asks the
 * payer for the rest on the checkout page.
 *
 * The three company fields are read on the billing address only, and
 * always together: a company is no use on an invoice half given, and the
 * gateway turns down an address that names one of them without the others.
 */
final readonly class Address
{
    public function __construct(
        public ?string $firstname = null,
        public ?string $lastname = null,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $address = null,
        public ?string $district = null,
        public ?string $province = null,
        public ?string $country = null,
        /** The company the customer is billed as. Billing address only. */
        public ?string $companyTitle = null,
        public ?string $taxNumber = null,
        public ?string $taxOffice = null,
    ) {}

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return array_filter([
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'district' => $this->district,
            'province' => $this->province,
            'country' => $this->country,
            'company_title' => $this->companyTitle,
            'tax_number' => $this->taxNumber,
            'tax_office' => $this->taxOffice,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
