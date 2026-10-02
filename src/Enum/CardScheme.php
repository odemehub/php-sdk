<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Enum;

/**
 * The network a card belongs to.
 */
enum CardScheme: string
{
    case Visa = 'visa';
    case Mastercard = 'mastercard';
    case AmericanExpress = 'american_express';
    case Troy = 'troy';
    case Discover = 'discover';
    case DinersClub = 'diners_club';
    case Jcb = 'jcb';
    case Unionpay = 'unionpay';
    case Maestro = 'maestro';
}
