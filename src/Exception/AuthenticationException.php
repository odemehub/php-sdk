<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Exception;

/**
 * The gateway did not accept the credentials (HTTP 401): the API key is not
 * the one issued to the team in the address, the request was not signed
 * with the matching secret, or its timestamp lies outside the five minutes
 * the gateway allows.
 */
class AuthenticationException extends OdemehubException
{
    //
}
