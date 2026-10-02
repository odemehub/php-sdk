<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Enum;

/**
 * Whether the money on a card is lent, drawn from an account or loaded
 * beforehand.
 */
enum CardType: string
{
    case Credit = 'credit';
    case Debit = 'debit';
    case Prepaid = 'prepaid';
}
