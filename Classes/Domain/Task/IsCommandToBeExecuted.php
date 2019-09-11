<?php
declare(strict_types=1);
namespace Sitegeist\Bitzer\Domain\Task;

use Neos\Flow\Annotations as Flow;

/**
 * The specification for whether a command is to be executed
 * @Flow\Proxy(false)
 */
final class IsCommandToBeExecuted
{
    public static function isSatisfiedByConstraintCheckResult(?ConstraintCheckResult $constraintCheckResult): bool
    {
        return !$constraintCheckResult // If no constraint check result is provided we assume that constraint check failures are directly thrown
            || $constraintCheckResult->hasSucceeded(); // If the constraint checks have succeeded we allow the command to be executed
    }
}
