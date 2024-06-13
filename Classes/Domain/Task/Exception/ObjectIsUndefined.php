<?php

declare(strict_types=1);

namespace Sitegeist\Bitzer\Domain\Task\Exception;

use Neos\Flow\Annotations as Flow;

/**
 * The exception to be thrown if no object was defined but was supposed to be
 */
#[Flow\Proxy(false)]
final class ObjectIsUndefined extends \DomainException
{
    public static function althoughExpected(): self
    {
        return new self('Object is undefined.', 1568206358);
    }
}
