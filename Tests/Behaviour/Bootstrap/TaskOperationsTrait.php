<?php
declare(strict_types=1);

/*
 * This file is part of the Sitegeist.Bitzer package.
 */

use Behat\Gherkin\Node\TableNode;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Flow\Http\Uri;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use PHPUnit\Framework\Assert;
use Sitegeist\Bitzer\Application\Bitzer;
use Sitegeist\Bitzer\Domain\Task\ActionStatusType;
use Sitegeist\Bitzer\Domain\Task\Command\CancelTask;
use Sitegeist\Bitzer\Domain\Task\Command\CompleteTask;
use Sitegeist\Bitzer\Domain\Task\Command\ReassignTask;
use Sitegeist\Bitzer\Domain\Task\Command\RescheduleTask;
use Sitegeist\Bitzer\Domain\Task\Command\ScheduleTask;
use Sitegeist\Bitzer\Domain\Task\Command\SetNewTaskTarget;
use Sitegeist\Bitzer\Domain\Task\Command\SetTaskProperties;
use Sitegeist\Bitzer\Domain\Task\NodeAddress;
use Sitegeist\Bitzer\Domain\Task\Schedule;
use Sitegeist\Bitzer\Domain\Task\ScheduledTime;
use Sitegeist\Bitzer\Domain\Task\TaskClassName;
use Sitegeist\Bitzer\Domain\Task\TaskIdentifier;
use Sitegeist\Bitzer\Domain\Task\TaskInterface;

/**
 * The task operations feature trait
 */
trait TaskOperationsTrait
{
    /**
     * @var Bitzer
     */
    private $bitzer;

    /**
     * @var Schedule
     */
    private $schedule;

    /**
     * @var Exception
     */
    private $lastCommandException;

    /**
     * @var TaskInterface
     */
    private $currentTask;

    abstract protected function getObjectManager(): ObjectManagerInterface;

    protected function setupTaskOperations()
    {
        $this->bitzer = $this->getObjectManager()->get(Bitzer::class);
        $this->schedule = $this->getObjectManager()->get(Schedule::class);
    }

    /**
     * @param TableNode $payloadTable
     * @return array
     * @throws Exception
     */
    protected function readPayloadTable(TableNode $payloadTable): array
    {
        $eventPayload = [];
        foreach ($payloadTable->getHash() as $line) {
            $value = json_decode($line['Value'], true);
            if ($value === null && json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception(sprintf('The value "%s" is no valid JSON string', $line['Value']), 1546522626);
            }
            $eventPayload[$line['Key']] = $value;
        }

        return $eventPayload;
    }

    /**
     * @When /^the command ScheduleTask is executed with payload:$/
     * @param TableNode $payloadTable
     * @throws Exception
     */
    public function theCommandScheduleTaskIsExecutedWithPayload(TableNode $payloadTable)
    {
        $commandArguments = $this->readPayloadTable($payloadTable);

        $command = new ScheduleTask(
            new TaskIdentifier($commandArguments['taskIdentifier']),
            new TaskClassName($commandArguments['taskClassName']),
            isset($commandArguments['scheduledTime']) ? ScheduledTime::createFromString($commandArguments['scheduledTime']) : null,
            $commandArguments['agent'],
            isset($commandArguments['object']) ? NodeAddress::fromArray($commandArguments['object']) : null,
            isset($commandArguments['target']) ? new Uri($commandArguments['target']) : null,
            $commandArguments['properties']
        );

        $this->bitzer->handleScheduleTask($command);
    }

    /**
     * @When /^the command ScheduleTask is executed with payload and exceptions are caught:$/
     * @param TableNode $payloadTable
     * @throws Exception
     */
    public function theCommandScheduleTaskIsExecutedWithPayloadAndExceptionsAreCaught(TableNode $payloadTable)
    {
        try {
            $this->theCommandScheduleTaskIsExecutedWithPayload($payloadTable);
        } catch (\Exception $exception) {
            $this->lastCommandException = $exception;
        }
    }

    /**
     * @When /^the command RescheduleTask is executed with payload:$/
     * @param TableNode $payloadTable
     * @throws Exception
     */
    public function theCommandRescheduleTaskIsExecutedWithPayload(TableNode $payloadTable)
    {
        $commandArguments = $this->readPayloadTable($payloadTable);

        $command = new RescheduleTask(
            new TaskIdentifier($commandArguments['taskIdentifier']),
            isset($commandArguments['scheduledTime']) ? ScheduledTime::createFromString($commandArguments['scheduledTime']) : null
        );

        $this->bitzer->handleRescheduleTask($command);
    }

    /**
     * @When /^the command RescheduleTask is executed with payload and exceptions are caught:$/
     * @param TableNode $payloadTable
     * @throws Exception
     */
    public function theCommandRescheduleTaskIsExecutedWithPayloadAndExceptionsAreCaught(TableNode $payloadTable)
    {
        try {
            $this->theCommandRescheduleTaskIsExecutedWithPayload($payloadTable);
        } catch (\Exception $exception) {
            $this->lastCommandException = $exception;
        }
    }

    /**
     * @When /^the command ReassignTask is executed with payload:$/
     * @param TableNode $payloadTable
     * @throws Exception
     */
    public function theCommandReassignTaskIsExecutedWithPayload(TableNode $payloadTable)
    {
        $commandArguments = $this->readPayloadTable($payloadTable);

        $command = new ReassignTask(
            new TaskIdentifier($commandArguments['taskIdentifier']),
            $commandArguments['agent']
        );

        $this->bitzer->handleReassignTask($command);
    }

    /**
     * @When /^the command ReassignTask is executed with payload and exceptions are caught:$/
     * @param TableNode $payloadTable
     * @throws Exception
     */
    public function theCommandReassignTaskIsExecutedWithPayloadAndExceptionsAreCaught(TableNode $payloadTable)
    {
        try {
            $this->theCommandReassignTaskIsExecutedWithPayload($payloadTable);
        } catch (\Exception $exception) {
            $this->lastCommandException = $exception;
        }
    }

    /**
     * @When /^the command SetTaskProperties is executed with payload:$/
     * @param TableNode $payloadTable
     * @throws Exception
     */
    public function theCommandSetTaskPropertiesIsExecutedWithPayload(TableNode $payloadTable)
    {
        $commandArguments = $this->readPayloadTable($payloadTable);

        $command = new SetTaskProperties(
            new TaskIdentifier($commandArguments['taskIdentifier']),
            $commandArguments['properties']
        );

        $this->bitzer->handleSetTaskProperties($command);
    }

    /**
     * @When /^the command SetTaskProperties is executed with payload and exceptions are caught:$/
     * @param TableNode $payloadTable
     * @throws Exception
     */
    public function theCommandSetTaskPropertiesIsExecutedWithPayloadAndExceptionsAreCaught(TableNode $payloadTable)
    {
        try {
            $this->theCommandSetTaskPropertiesIsExecutedWithPayload($payloadTable);
        } catch (\Exception $exception) {
            $this->lastCommandException = $exception;
        }
    }

    /**
     * @When /^the command CancelTask is executed with payload:$/
     * @param TableNode $payloadTable
     * @throws Exception
     */
    public function theCommandCancelTaskIsExecutedWithPayload(TableNode $payloadTable)
    {
        $commandArguments = $this->readPayloadTable($payloadTable);

        $command = new CancelTask(
            new TaskIdentifier($commandArguments['taskIdentifier'])
        );

        $this->bitzer->handleCancelTask($command);
    }

    /**
     * @When /^the command CancelTask is executed with payload and exceptions are caught:$/
     * @param TableNode $payloadTable
     * @throws Exception
     */
    public function theCommandCancelTaskIsExecutedWithPayloadAndExceptionsAreCaught(TableNode $payloadTable)
    {
        try {
            $this->theCommandCancelTaskIsExecutedWithPayload($payloadTable);
        } catch (\Exception $exception) {
            $this->lastCommandException = $exception;
        }
    }

    /**
     * @When /^the command CompleteTask is executed with payload:$/
     * @param TableNode $payloadTable
     * @throws Exception
     */
    public function theCommandCompleteTaskIsExecutedWithPayload(TableNode $payloadTable)
    {
        $commandArguments = $this->readPayloadTable($payloadTable);

        $command = new CompleteTask(
            new TaskIdentifier($commandArguments['taskIdentifier'])
        );

        $this->bitzer->handleCompleteTask($command);
    }

    /**
     * @When /^the command CompleteTask is executed with payload and exceptions are caught:$/
     * @param TableNode $payloadTable
     * @throws Exception
     */
    public function theCommandCompleteTaskIsExecutedWithPayloadAndExceptionsAreCaught(TableNode $payloadTable)
    {
        try {
            $this->theCommandCompleteTaskIsExecutedWithPayload($payloadTable);
        } catch (\Exception $exception) {
            $this->lastCommandException = $exception;
        }
    }

    /**
     * @When /^the command SetNewTaskTarget is executed with payload:$/
     * @param TableNode $payloadTable
     * @throws Exception
     */
    public function theCommandSetNewTaskTargetIsExecutedWithPayload(TableNode $payloadTable)
    {
        $commandArguments = $this->readPayloadTable($payloadTable);

        $command = new SetNewTaskTarget(
            new TaskIdentifier($commandArguments['taskIdentifier']),
            isset($commandArguments['target']) ? new Uri($commandArguments['target']) : null
        );

        $this->bitzer->handleSetNewTaskTarget($command);
    }

    /**
     * @When /^the command SetNewTaskTarget is executed with payload and exceptions are caught:$/
     * @param TableNode $payloadTable
     * @throws Exception
     */
    public function theCommandSetNewTaskTargetIsExecutedWithPayloadAndExceptionsAreCaught(TableNode $payloadTable)
    {
        try {
            $this->theCommandSetNewTaskTargetIsExecutedWithPayload($payloadTable);
        } catch (\Exception $exception) {
            $this->lastCommandException = $exception;
        }
    }

    /**
     * @Then /^the last command should have thrown an exception of type "([^"]*)"$/
     * @param string $shortExceptionName
     * @throws ReflectionException
     */
    public function theLastCommandShouldHaveThrown(string $shortExceptionName)
    {
        Assert::assertNotNull($this->lastCommandException, 'Command did not throw exception');
        $lastCommandExceptionShortName = (new ReflectionClass($this->lastCommandException))->getShortName();
        Assert::assertSame($shortExceptionName, $lastCommandExceptionShortName, sprintf('Actual exception: %s (%s): %s', get_class($this->lastCommandException), $this->lastCommandException->getCode(), $this->lastCommandException->getMessage()));
    }

    /**
     * @Then /^I expect the task "([^"]*)" to exist$/
     * @param string $taskIdentifier
     */
    public function iExpectTheTaskToExist(string $taskIdentifier): void
    {
        $taskIdentifier = new TaskIdentifier($taskIdentifier);

        $this->currentTask = $this->schedule->findByIdentifier($taskIdentifier);

        Assert::assertNotNull($this->currentTask, sprintf('Task "%s" was not found in the schedule.', $taskIdentifier));
    }

    /**
     * @Then /^I expect the task "([^"]*)" not to exist$/
     * @param string $taskIdentifier
     */
    public function iExpectTheTaskNotToExist(string $taskIdentifier): void
    {
        $taskIdentifier = new TaskIdentifier($taskIdentifier);

        $unexpectedTask = $this->schedule->findByIdentifier($taskIdentifier);

        Assert::assertNull($unexpectedTask, sprintf('Task "%s" was found in the schedule but was not supposed to be.', $taskIdentifier));
    }

    /**
     * @Then /^I expect the schedule to consist of exactly (\d+) tasks$/
     * @param int $expectedNumberOfTasks
     * @throws \Doctrine\DBAL\DBALException
     */
    public function iExpectTheGraphProjectionToConsistOfExactlyNodes(int $expectedNumberOfTasks)
    {
        $actualNumberOfTasks = count($this->schedule->findAll());
        Assert::assertSame($expectedNumberOfTasks, $actualNumberOfTasks, 'Schedule consists of ' . $actualNumberOfTasks . ' tasks, expected were ' . $expectedNumberOfTasks . '.');
    }

    /**
     * @Then /^I expect this task to be of class "([^"]*)"$/
     * @param string $expectedClassName
     */
    public function iExpectThisTaskToBeOfClass(string $expectedClassName)
    {
        $actualClassName = get_class($this->currentTask);
        Assert::assertEquals($expectedClassName, $actualClassName, 'The current task is of type ' . $actualClassName . ', expected was ' . $expectedClassName);
    }

    /**
     * @Then /^I expect this task to have action status "([^"]*)"$/
     * @param string $expectedActionStatus
     */
    public function iExpectThisTaskToHaveActionStatus(string $expectedActionStatus)
    {
        $expectedActionStatus = ActionStatusType::createFromString($expectedActionStatus);
        $actualActionStatus = $this->currentTask->getActionStatus();
        Assert::assertTrue($expectedActionStatus->equals($actualActionStatus), 'The current task has action status ' . $actualActionStatus . ', expected was ' . $expectedActionStatus);
    }

    /**
     * @Then /^I expect this task to be scheduled to "([^"]*)"$/
     * @param string $expectedScheduledTime
     */
    public function iExpectThisTaskToBeScheduledTo(string $expectedScheduledTime)
    {
        $actualScheduledTime = $this->currentTask->getScheduledTime()->format('c');
        Assert::assertEquals($expectedScheduledTime, $actualScheduledTime, 'The current task is scheduled to  ' . $actualScheduledTime . ', expected was ' . $expectedScheduledTime);
    }

    /**
     * @Then /^I expect this task to be assigned to "([^"]*)"$/
     * @param string $expectedAgent
     */
    public function iExpectThisTaskToBeAssignedTo(string $expectedAgent)
    {
        $actualAgent = $this->currentTask->getAgent();
        Assert::assertEquals($expectedAgent, $actualAgent, 'The current task is assigned to  ' . $actualAgent . ', expected was ' . $expectedAgent);
    }

    /**
     * @Then /^I expect this task to be about '([^']*)'$/
     * @param string $expectedObject
     */
    public function iExpectThisTaskToBeAbout(string $expectedObject)
    {
        $expectedObject = NodeAddress::fromArray(json_decode($expectedObject, true));
        Assert::assertInstanceOf(NodeInterface::class, $this->currentTask->getObject(), 'The current task is about nothing, expected was ' . $expectedObject);
        $actualObject = NodeAddress::fromNode($this->currentTask->getObject());
        Assert::assertTrue($expectedObject->equals($actualObject), 'The current task is about  ' . $actualObject . ', expected was ' . $expectedObject);
    }

    /**
     * @Then /^I expect this task to have the target "([^"]*)"$/
     * @param string $expectedTarget
     */
    public function iExpectThisTaskToHaveTheTarget(string $expectedTarget)
    {
        $expectedTarget = new Uri($expectedTarget);
        $actualTarget = $this->currentTask->getTarget();
        Assert::assertSame((string) $expectedTarget, (string) $actualTarget, 'The current task has the target  ' . $actualTarget . ', expected was ' . $expectedTarget);
    }

    /**
     * @Then /^I expect this task to have the properties:$/
     * @param TableNode $expectedProperties
     */
    public function iExpectThisTaskToHaveTheProperties(TableNode $expectedProperties)
    {
        $actualProperties = $this->currentTask->getProperties();
        foreach ($expectedProperties->getHash() as $row) {
            $propertyName = $row['Key'];
            $expectedPropertyValue = $row['Value'];
            Assert::assertArrayHasKey($propertyName, $actualProperties, 'The current task misses the property "' . $propertyName . '".');
            $actualPropertyValue = $actualProperties[$propertyName];
            Assert::assertEquals($expectedPropertyValue, $actualPropertyValue, 'The current task\'s value for property "' . $propertyName . '" is "' . $actualPropertyValue . '", expected was "' . $expectedPropertyValue . '"');
        }
    }
}
