<?php
declare(strict_types=1);
namespace Sitegeist\Bitzer\Domain\Task\Exception;

/**
 * The exception to be thrown if no object was defined but was supposed to be
 */
final class ObjectIsUndefined extends \DomainException
{
    public static function althoughExpected(): ObjectIsUndefined
    {
        return new static('Object is undefined.', 1568206358);
    }
}
