<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Enum;

/**
 * What a payment link lets the payer pay: the lines the merchant wrote
 * (`Fixed`), any amount they write themselves (`Custom`), one of the
 * amounts the link offers (`Predefined`), or one of those or an amount of
 * their own (`PredefinedAndCustom`). Every type but `Fixed` is paid as one
 * line under the link's item name.
 */
enum AmountType: string
{
    case Fixed = 'fixed';
    case Custom = 'custom';
    case Predefined = 'predefined';
    case PredefinedAndCustom = 'predefined_and_custom';
}
