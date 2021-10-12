<?php declare(strict_types=1);
namespace Sitegeist\Bitzer\Domain\Task\Exception;

use Neos\Flow\Annotations as Flow;

/**
 * The exception to be thrown if a given short identifier defines no class implementation
 */
final class ShortTypeDefinesNoTask extends \DomainException
{
}
