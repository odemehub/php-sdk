<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Exception;

/**
 * The credentials were fine but the team may not do this (HTTP 403): it has
 * an unpaid balance, or the module the endpoint belongs to is turned off.
 * The message says which. ödemehub turns modules on; get in touch to have
 * one opened.
 */
class ForbiddenException extends OdemehubException
{
    //
}
