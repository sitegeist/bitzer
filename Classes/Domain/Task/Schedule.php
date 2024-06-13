<?php

declare(strict_types=1);

namespace Sitegeist\Bitzer\Domain\Task;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Exception as DriverException;
use Doctrine\DBAL\Exception as DbalException;
use Doctrine\DBAL\Types\Types;
use GuzzleHttp\Psr7\Uri;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\ObjectManagement\ObjectManager;
use Neos\Flow\Persistence\Doctrine\ConnectionFactory;
use Psr\Http\Message\UriInterface;
use Sitegeist\Bitzer\Domain\Agent\AgentIdentifier;
use Sitegeist\Bitzer\Domain\Agent\Agents;
use Sitegeist\Bitzer\Domain\Task\Command\ScheduleTask;
use Sitegeist\Bitzer\Domain\Task\Generic\GenericTaskFactory;
use Sitegeist\Bitzer\Domain\Agent\Agent;
use Sitegeist\Bitzer\Domain\Agent\AgentRepository;

/**
 * The schedule, the repository for tasks
 */
#[Flow\Scope('singleton')]
final class Schedule
{
    private const TABLE_NAME = 'sitegeist_bitzer_domain_task_task';

    private readonly Connection $databaseConnection;

    /**
     * @param array<class-string<TaskInterface>,class-string<TaskFactoryInterface>> $factoryMapping
     */
    public function __construct(
        private readonly array $factoryMapping,
        ConnectionFactory $connectionFactory,
        private readonly AgentRepository $agentRepository,
        private readonly ObjectManager $objectManager,
    ) {
        $this->databaseConnection = $connectionFactory->create();
    }

    /**
     * @throws DriverException
     * @throws DbalException
     */
    final public function findByIdentifier(TaskIdentifier $identifier): ?TaskInterface
    {
        $tableRow = $this->databaseConnection->executeQuery(
            'SELECT * FROM ' . self::TABLE_NAME . '
                    WHERE identifier = :identifier',
            [
                'identifier' => (string)$identifier
            ]
        )->fetchAssociative();

        return $tableRow
            ? $this->createTaskFromTableRow($tableRow)
            : null;
    }

    /**
     * @throws DriverException
     * @throws DbalException
     */
    final public function findAll(): Tasks
    {
        $rawDataSet = $this->databaseConnection->executeQuery(
            'SELECT * FROM ' . self::TABLE_NAME
        )->fetchAllAssociative();

        return $this->createTasksFromTableRows($rawDataSet);
    }

    /**
     * @throws DriverException
     * @throws DbalException
     */
    final public function findAllOrdered(): Tasks
    {
        $rawDataSet = $this->databaseConnection->executeQuery(
            'SELECT * FROM ' . self::TABLE_NAME . '
                    ORDER BY scheduledtime ASC'
        )->fetchAllAssociative();

        return $this->createTasksFromTableRows($rawDataSet);
    }

    /**
     * @return array<string,array<int,TaskInterface>>
     * @throws DbalException
     * @throws DriverException
     */
    final public function findPastDueDueAndUpcoming(\DateInterval $upcomingInterval, ?Agents $agents = null): array
    {
        $now = ScheduledTime::now();
        $referenceDate = $now->add($upcomingInterval);

        $query = 'SELECT * FROM ' . self::TABLE_NAME . '
                    WHERE scheduledtime <= :referenceDate
                    AND actionstatus IN (:actionStatusTypes)';
        $parameters = [
            'referenceDate' => $referenceDate,
            'actionStatusTypes' => [
                ActionStatusType::TYPE_POTENTIAL->value,
                ActionStatusType::TYPE_ACTIVE->value,
            ]
        ];
        $types = [
            'referenceDate' => Types::DATETIME_IMMUTABLE,
            'actionStatusTypes' => Connection::PARAM_STR_ARRAY
        ];

        if ($agents) {
            $parameters['agentIdentifiers'] = $agents->getIdentifiers();
            $types['agentIdentifiers'] = Connection::PARAM_STR_ARRAY;
            $query .= ' AND agent IN (:agentIdentifiers)';
        }
        $query .= ' ORDER BY scheduledtime ASC';

        $rawDataSet = $this->databaseConnection->executeQuery(
            $query,
            $parameters,
            $types
        )->fetchAllAssociative();
        $tasks = $this->createTasksFromTableRows($rawDataSet);

        $groupedTasks = [
            TaskDueStatusType::STATUS_PAST_DUE->value => [],
            TaskDueStatusType::STATUS_DUE->value => [],
            TaskDueStatusType::STATUS_UPCOMING->value => []
        ];
        foreach ($tasks as $task) {
            $groupedTasks[TaskDueStatusType::forTask($task)->value][] = $task;
        }

        return $groupedTasks;
    }

    /**
     * @throws DriverException
     * @throws DbalException
     */
    final public function countDue(?Agents $agents = null): int
    {
        $query = 'SELECT COUNT(*) FROM ' . self::TABLE_NAME . '
                    WHERE actionstatus IN (:actionStatusTypes)
                    AND TO_DAYS(scheduledTime) = TO_DAYS(NOW())';

        $parameters = [
            'actionStatusTypes' => [
                ActionStatusType::TYPE_POTENTIAL->value,
                ActionStatusType::TYPE_ACTIVE->value,
            ]
        ];

        $types = [
            'actionStatusTypes' => Connection::PARAM_STR_ARRAY
        ];

        if ($agents) {
            $parameters['agentIdentifiers'] = $agents->getIdentifiers();
            $types['agentIdentifiers'] = Connection::PARAM_STR_ARRAY;
            $query .= ' AND agent IN (:agentIdentifiers)';
        }

        $rawDataSet = $this->databaseConnection->executeQuery(
            $query,
            $parameters,
            $types
        )->fetchAllAssociative();

        return (int) $rawDataSet[0]['COUNT(*)'];
    }

    /**
     * @throws DriverException
     * @throws DbalException
     */
    final public function countPastDue(?Agents $agents = null): int
    {
        $query = 'SELECT COUNT(*) FROM ' . self::TABLE_NAME . '
                    WHERE actionstatus IN (:actionStatusTypes)
                    AND scheduledTime < NOW()
                    AND TO_DAYS(scheduledTime) <> TO_DAYS(NOW())';

        $parameters = [
            'actionStatusTypes' => [
                ActionStatusType::TYPE_POTENTIAL->value,
                ActionStatusType::TYPE_ACTIVE->value,
            ]
        ];

        $types = [
            'actionStatusTypes' => Connection::PARAM_STR_ARRAY
        ];

        if ($agents) {
            $parameters['agentIdentifiers'] = $agents->getIdentifiers();
            $types['agentIdentifiers'] = Connection::PARAM_STR_ARRAY;
            $query .= ' AND agent IN (:agentIdentifiers)';
        }

        $rawDataSet = $this->databaseConnection->executeQuery(
            $query,
            $parameters,
            $types
        )->fetchAllAssociative();

        return (int) $rawDataSet[0]['COUNT(*)'];
    }

    /**
     * @throws DriverException
     * @throws DbalException
     */
    final public function countUpcoming(\DateInterval $upcomingInterval, ?Agents $agents = null): int
    {
        $now = ScheduledTime::now();
        $referenceDate = $now->add($upcomingInterval);

        $query = 'SELECT COUNT(*) FROM ' . self::TABLE_NAME . '
            WHERE actionstatus IN (:actionStatusTypes)
            AND scheduledTime <= :referenceDate
            AND scheduledTime > NOW()
            AND TO_DAYS(scheduledTime) <> TO_DAYS(NOW())';

        $parameters = [
            'referenceDate' => $referenceDate,
            'actionStatusTypes' => [
                ActionStatusType::TYPE_POTENTIAL->value,
                ActionStatusType::TYPE_ACTIVE->value,
            ]
        ];

        $types = [
            'referenceDate' => Types::DATETIME_IMMUTABLE,
            'actionStatusTypes' => Connection::PARAM_STR_ARRAY
        ];

        if ($agents) {
            $parameters['agentIdentifiers'] = $agents->getIdentifiers();
            $types['agentIdentifiers'] = Connection::PARAM_STR_ARRAY;
            $query .= ' AND agent IN (:agentIdentifiers)';
        }

        $rawDataSet = $this->databaseConnection->executeQuery(
            $query,
            $parameters,
            $types
        )->fetchAllAssociative();

        return (int) $rawDataSet[0]['COUNT(*)'];
    }

    /**
     * @throws DriverException
     * @throws DbalException
     */
    final public function findPotentialTasksOfClassForObject(TaskClassName $taskClassName, NodeAddress $object): Tasks
    {
        $rawDataSet = $this->databaseConnection->executeQuery(
            'SELECT * FROM ' . self::TABLE_NAME . '
                    WHERE classname = :taskClassName
                    AND object = :object
                    AND actionstatus = :actionStatusType',
            [
                'taskClassName' => $taskClassName,
                'object' => $object,
                'actionStatusType' => ActionStatusType::TYPE_POTENTIAL->value,
            ]
        )->fetchAllAssociative();

        return $this->createTasksFromTableRows($rawDataSet);
    }

    /**
     * @throws DriverException
     * @throws DbalException
     */
    final public function findActiveOrPotentialTasksForObject(NodeAddress $object, ?TaskClassName $taskClassName = null): Tasks
    {
        $query = 'SELECT * FROM ' . self::TABLE_NAME . '
                    WHERE object = :object
                    AND actionstatus IN (:actionStatusTypes)';
        $params = [
            'object' => $object,
            'actionStatusTypes' => [
                ActionStatusType::TYPE_POTENTIAL->value,
                ActionStatusType::TYPE_ACTIVE->value,
            ]
        ];
        if ($taskClassName) {
            $query .= '
                AND classname = :taskClassName';
            $params['taskClassName'] = $taskClassName->value;
        }

        $tableRows = $this->databaseConnection->executeQuery(
            $query,
            $params,
            [
                'actionStatusTypes' => Connection::PARAM_STR_ARRAY
            ]
        )->fetchAllAssociative();

        return $this->createTasksFromTableRows($tableRows);
    }

    /**
     * @return array<TaskIdentifier>
     * @throws DriverException
     * @throws DbalException
     */
    final public function findActiveOrPotentialTaskIdsForObject(NodeAddress $object, ?TaskClassName $taskClassName = null): array
    {
        $query = 'SELECT * FROM ' . self::TABLE_NAME . '
                    WHERE object = :object
                    AND actionstatus IN (:actionStatusTypes)';
        $params = [
            'object' => $object,
            'actionStatusTypes' => [
                ActionStatusType::TYPE_POTENTIAL->value,
                ActionStatusType::TYPE_ACTIVE->value,
            ]
        ];
        if ($taskClassName) {
            $query .= '
                AND classname = :taskClassName';
            $params['taskClassName'] = $taskClassName->value;
        }

        $tableRows = $this->databaseConnection->executeQuery(
            $query,
            $params,
            [
                'actionStatusTypes' => Connection::PARAM_STR_ARRAY
            ]
        )->fetchAllAssociative();

        return array_map(
            fn (array $taskRecord): TaskIdentifier => new TaskIdentifier($taskRecord['identifier']),
            $tableRows
        );
    }

    /**
     * @throws DbalException
     */
    final public function scheduleTask(ScheduleTask $command, Agent $agent): void
    {
        $this->databaseConnection->insert(
            self::TABLE_NAME,
            [
                'identifier' => (string)$command->identifier,
                'classname' => (string)$command->className,
                'properties' => $command->properties,
                'scheduledtime' => $command->scheduledTime,
                'actionstatus' => ActionStatusType::TYPE_POTENTIAL,
                'agent' => $agent,
                'object' => $command->object ? json_encode($command->object) : null,
                'target' => $command->target
            ],
            [
                'scheduledtime' => Types::DATETIME_IMMUTABLE,
                'properties' => Types::JSON
            ]
        );
    }

    /**
     * @throws DbalException
     */
    final public function rescheduleTask(TaskIdentifier $taskIdentifier, ?\DateTimeImmutable $scheduledTime): void
    {
        $this->databaseConnection->update(
            self::TABLE_NAME,
            [
                'scheduledtime' => $scheduledTime,
            ],
            [
                'identifier' => $taskIdentifier,
            ],
            [
                'scheduledtime' => Types::DATETIME_IMMUTABLE
            ]
        );
    }

    /**
     * @throws DbalException
     */
    final public function reassignTask(TaskIdentifier $taskIdentifier, Agent $agent): void
    {
        $this->databaseConnection->update(
            self::TABLE_NAME,
            [
                'agent' => $agent,
            ],
            [
                'identifier' => $taskIdentifier,
            ]
        );
    }

    /**
     * @throws DbalException
     */
    final public function setTaskObject(TaskIdentifier $taskIdentifier, ?NodeAddress $object): void
    {
        $this->databaseConnection->update(
            self::TABLE_NAME,
            [
                'object' => $object ? json_encode($object) : null,
            ],
            [
                'identifier' => $taskIdentifier,
            ]
        );
    }

    /**
     * @throws DbalException
     */
    final public function setTaskTarget(TaskIdentifier $taskIdentifier, ?UriInterface $target): void
    {
        $this->databaseConnection->update(
            self::TABLE_NAME,
            [
                'target' => $target,
            ],
            [
                'identifier' => $taskIdentifier,
            ]
        );
    }

    /**
     * @param array<string,mixed> $properties
     * @throws DbalException
     */
    final public function setTaskProperties(TaskIdentifier $taskIdentifier, array $properties): void
    {
        $this->databaseConnection->update(
            self::TABLE_NAME,
            [
                'properties' => $properties,
            ],
            [
                'identifier' => $taskIdentifier,
            ],
            [
                'properties' => Types::JSON
            ]
        );
    }

    /**
     * @throws DbalException
     */
    final public function cancelTask(TaskIdentifier $taskIdentifier): void
    {
        $this->databaseConnection->executeQuery(
            'DELETE FROM ' . self::TABLE_NAME . ' WHERE identifier = :identifier',
            [
                'identifier' => (string) $taskIdentifier
            ]
        );
    }

    /**
     * @throws DbalException
     */
    final public function updateTaskActionStatus(TaskIdentifier $taskIdentifier, ActionStatusType $actionStatus): void
    {
        $this->databaseConnection->update(
            self::TABLE_NAME,
            [
                'actionstatus' => $actionStatus,
            ],
            [
                'identifier' => $taskIdentifier
            ]
        );
        $this->emitTaskActionStatusUpdated($taskIdentifier, $actionStatus);
    }

    /**
     * @Flow\Signal
     * @param TaskIdentifier $taskIdentifier
     * @param ActionStatusType|null $actionStatus
     */
    public function emitTaskActionStatusUpdated(TaskIdentifier $taskIdentifier, ActionStatusType $actionStatus = null): void
    {
    }

    /**
     * @param array<int,mixed> $tableRows
     */
    private function createTasksFromTableRows(array $tableRows): Tasks
    {
        return new Tasks(...array_filter(array_map(function (array $tableRow): ?TaskInterface {
            return $this->createTaskFromTableRow($tableRow);
        }, $tableRows)));
    }

    /**
     * @param array<string,mixed> $tableRow
     */
    private function createTaskFromTableRow(array $tableRow): ?TaskInterface
    {
        $className = TaskClassName::createFromString($tableRow['classname']);
        $factory = $this->resolveFactory($className);
        $agent = $this->agentRepository->findByIdentifier(AgentIdentifier::fromString($tableRow['agent']));
        if (!$agent) {
            return null;
        }

        $object = null;
        if (isset($tableRow['object']) && !empty($tableRow['object'])) {
            $object = NodeAddress::fromArray(json_decode($tableRow['object'], true));
        }

        return $factory->createFromRawData(
            new TaskIdentifier($tableRow['identifier']),
            $className,
            json_decode($tableRow['properties'], true),
            ScheduledTime::createFromDatabaseValue($tableRow['scheduledtime']),
            ActionStatusType::from($tableRow['actionstatus']),
            $agent,
            $object,
            isset($tableRow['target'])
                ? new Uri($tableRow['target'])
                : null
        );
    }

    private function resolveFactory(TaskClassName $className): TaskFactoryInterface
    {
        /** @var TaskFactoryInterface $taskFactory */
        $taskFactory = $this->objectManager->get(
            $this->factoryMapping[(string)$className] ?? GenericTaskFactory::class
        );

        return $taskFactory;
    }
}
