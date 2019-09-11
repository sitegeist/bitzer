<?php
declare(strict_types=1);
namespace Sitegeist\Bitzer\Domain\Task;

use Neos\Flow\Annotations as Flow;
use Sitegeist\Bitzer\Domain\Task\Command\CancelTask;
use Sitegeist\Bitzer\Domain\Task\Command\CompleteTask;
use Sitegeist\Bitzer\Domain\Task\Command\ReassignTask;
use Sitegeist\Bitzer\Domain\Task\Command\RescheduleTask;
use Sitegeist\Bitzer\Domain\Task\Command\ScheduleTask;
use Sitegeist\Bitzer\Domain\Task\Command\SetNewTaskObject;
use Sitegeist\Bitzer\Domain\Task\Command\SetNewTaskTarget;
use Sitegeist\Bitzer\Domain\Task\Command\SetTaskProperties;

/**
 * The interface to be implemented by constraint check plugins.
 *
 * These methods will be called by the Bitzer command handler after its own constraint checks.
 *
 * DomainExceptions occurring during the plugin constraint checks must be registered in the constraint check result if given, otherwise thrown.
 */
interface ConstraintCheckPluginInterface
{
    public function checkScheduleTask(ScheduleTask $command, ConstraintCheckResult $constraintCheckResult = null): void;

    public function checkRescheduleTask(RescheduleTask $command, ConstraintCheckResult $constraintCheckResult = null): void;

    public function checkReassignTask(ReassignTask $command, ConstraintCheckResult $constraintCheckResult = null): void;

    public function checkSetNewTaskTarget(SetNewTaskTarget $command, ConstraintCheckResult $constraintCheckResult = null): void;

    public function checkSetNewTaskObject(SetNewTaskObject $command, ConstraintCheckResult $constraintCheckResult = null): void;

    public function checkSetTaskProperties(SetTaskProperties $command, ConstraintCheckResult $constraintCheckResult = null): void;

    public function checkCancelTask(CancelTask $command, ConstraintCheckResult $constraintCheckResult = null): void;

    public function checkCompleteTask(CompleteTask $command, ConstraintCheckResult $constraintCheckResult = null): void;
}
