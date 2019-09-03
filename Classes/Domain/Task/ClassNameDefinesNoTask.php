<?php
namespace Sitegeist\Bitzer\Domain\Task;

use Neos\Flow\Annotations as Flow;

/**
 * The exception to be thrown if a given class name does not define a class that implements the task interface
 */
final class ClassNameDefinesNoTask extends \DomainException
{
}
