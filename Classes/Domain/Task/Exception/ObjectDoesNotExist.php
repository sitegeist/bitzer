<?php
declare(strict_types=1);
namespace Sitegeist\Bitzer\Domain\Task\Exception;

/**
 * The exception to be thrown if a requested object does not exist but is supposed to
 */
final class ObjectDoesNotExist extends \DomainException
{
}
