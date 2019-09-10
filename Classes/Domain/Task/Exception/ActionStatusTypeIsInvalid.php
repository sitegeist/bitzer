<?php
declare(strict_types=1);
namespace Sitegeist\Bitzer\Domain\Task\Exception;

use Neos\Flow\Annotations as Flow;

/**
 * The exception to be thrown if an action status type was tried to be initialized with an invalid value
 */
final class ActionStatusTypeIsInvalid extends \DomainException
{
}
