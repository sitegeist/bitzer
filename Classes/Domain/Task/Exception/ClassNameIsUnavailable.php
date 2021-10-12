<?php declare(strict_types=1);
namespace Sitegeist\Bitzer\Domain\Task\Exception;

use Neos\Flow\Annotations as Flow;

/**
 * The exception to be thrown if a given class name is not available
 */
final class ClassNameIsUnavailable extends \DomainException
{
}
