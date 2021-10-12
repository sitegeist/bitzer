<?php declare(strict_types=1);
namespace Sitegeist\Bitzer\Domain\Task\Exception;

use Neos\Flow\Annotations as Flow;

/**
 * The exception to be thrown if a given class name does not define a class that implements the task interface
 */
final class ClassNameDefinesNoTask extends \DomainException
{
}
