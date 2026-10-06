<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Enum;

/**
 * How a payment link whose amount the payer picks reads its tax rate
 * against what they pay: split out of it (`Inclusive`: 100 paid is 83.33
 * and 16.67 tax at 20%), or added on top of it (`Exclusive`: 100 written
 * is 120 charged). A link of lines keeps the tax inside each line.
 */
enum TaxMode: string
{
    case Inclusive = 'inclusive';
    case Exclusive = 'exclusive';
}
