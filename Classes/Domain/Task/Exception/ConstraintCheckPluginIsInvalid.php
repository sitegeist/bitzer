<?php declare(strict_types=1);
namespace Sitegeist\Bitzer\Domain\Task\Exception;

/**
 * The exception to be thrown if a given constraint check plugin is invalid
 */
final class ConstraintCheckPluginIsInvalid extends \DomainException
{
    public static function becauseItDoesNotImplementTheRequiredInterface(string $className): self
    {
        return new self('Given constraint check plugin ' . $className . ' does not implement the required interface.', 1568213486);
    }

    public static function becauseItIsNotImplemented(string $className): self
    {
        return new self('Given constraint check plugin ' . $className . ' is not implemented.', 1568213523);
    }
}
