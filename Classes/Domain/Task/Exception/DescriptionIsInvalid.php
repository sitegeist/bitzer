<?php
declare(strict_types=1);
namespace Sitegeist\Bitzer\Domain\Task\Exception;

/**
 * The exception to be thrown if a task was tried to be scheduled or modified without a valid description
 */
final class DescriptionIsInvalid extends \DomainException
{
}
