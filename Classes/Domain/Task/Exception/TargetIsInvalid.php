<?php

declare(strict_types=1);

namespace Sitegeist\Bitzer\Domain\Task\Exception;

use Neos\Flow\Annotations as Flow;

/**
 * The exception to be thrown if an invalid target was defined
 */
#[Flow\Proxy(false)]
final class TargetIsInvalid extends \DomainException
{
    public static function mustBeAnAbsoluteUri(): self
    {
        return new self('The target must be an absolute URI.', 1567764586);
    }
}
