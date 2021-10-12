<?php declare(strict_types=1);
namespace Sitegeist\Bitzer\Domain\Task;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use GuzzleHttp\Psr7\Uri;
use Neos\Flow\Annotations as Flow;
use Neos\Neos\Domain\Service\ContentDimensionPresetSourceInterface;
use Psr\Http\Message\UriInterface;
use Sitegeist\Bitzer\Domain\Task\Command\ScheduleTask;
use Sitegeist\Bitzer\Domain\Task\Generic\GenericTaskFactory;
use Sitegeist\Bitzer\Infrastructure\DbalClient;
use Sitegeist\Bitzer\Domain\Agent\Agent;
use Sitegeist\Bitzer\Domain\Agent\AgentRepository;
use Sitegeist\Bitzer\Domain\Task\Exception\AgentDoesNotExist;

/**
 * The schedule, the repository for tasks
 * @Flow\Scope("singleton")
 */
class Schedule
{
    const TABLE_NAME = 'sitegeist_bitzer_domain_task_task';

    /**
     * @Flow\InjectConfiguration(path="factories")
     * @var array
     */
    protected $factoryMapping;

    /**
     * @Flow\Inject
     * @var DbalClient
     */
    protected $databaseClient;

    /**
     * @Flow\Inject
     * @var ContentDimensionPresetSourceInterface
     */
    protected $contentDimensionPresetSource;

    /**
     * @Flow\Inject
     * @var AgentRepository
     */
    protected $agentRepository;

    final public function findByIdentifier(TaskIdentifier $identifier): ?TaskInterface
    {
        $tableRow = $this->getDatabaseConnection()->executeQuery(
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

    final public function findAll(): Tasks
    {
        $rawDataSet = $this->getDatabaseConnection()->executeQuery(
            'SELECT * FROM ' . self::TABLE_NAME
        )->fetchAllAssociative();

        return $this->createTasksFromTableRows($rawDataSet);
    }

    final public function findAllOrdered(): Tasks
    {
        $rawDataSet = $this->getDatabaseConnection()->executeQuery(
            'SELECT * FROM ' . self::TABLE_NAME . '
                ORDER BY scheduledtime ASC'
        )->fetchAllAssociative();

        return $this->createTasksFromTableRows($rawDataSet);
    }

    /**
     * @param \DateInterval $upcomingInterval
     * @param array|null $agentIdentifiers
     * @return array|TaskInterface[][]
     * @throws \Doctrine\DBAL\DBALException
     */
    final public function findPastDueDueAndUpcoming(\DateInterval $upcomingInterval, ?array $agentIdentifiers = null): array
    {
        $now = ScheduledTime::now();
        $referenceDate = $now->add($upcomingInterval);

        $query = 'SELECT * FROM ' . self::TABLE_NAME . '
            WHERE scheduledtime <= :referenceDate
            AND actionstatus IN (:actionStatusTypes)';
        $parameters = [
            'referenceDate' => $referenceDate,
            'actionStatusTypes' => [
                ActionStatusType::TYPE_POTENTIAL,
                ActionStatusType::TYPE_ACTIVE
            ]
        ];
        $types = [
            'referenceDate' => Types::DATETIME_IMMUTABLE,
            'actionStatusTypes' => Connection::PARAM_STR_ARRAY
        ];

        if ($agentIdentifiers) {
            $parameters['agentIdentifiers'] = $agentIdentifiers;
            $types['agentIdentifiers'] = Connection::PARAM_STR_ARRAY;
            $query .= ' AND agent IN (:agentIdentifiers)';
        }
        $query .= ' ORDER BY scheduledtime ASC';

        $rawDataSet = $this->getDatabaseConnection()->executeQuery(
            $query,
            $parameters,
            $types
        )->fetchAllAssociative();
        $tasks = $this->createTasksFromTableRows($rawDataSet);

        $groupedTasks = [
            TaskDueStatusType::STATUS_PAST_DUE => [],
            TaskDueStatusType::STATUS_DUE => [],
            TaskDueStatusType::STATUS_UPCOMING => []
        ];
        foreach ($tasks as $task) {
            $groupedTasks[(string)TaskDueStatusType::forTask($task)][] = $task;
        }

        return $groupedTasks;
    }

    final function countDue(?array $agentIdentifiers = null): int
    {
        $sql = 'SELECT COUNT(*) FROM ' . self::TABLE_NAME . '
            WHERE
                actionstatus IN (:actionStatusTypes)
            AND TO_DAYS(scheduledTime) = TO_DAYS(NOW())';

        $parameters = [
            'actionStatusTypes' => [
                ActionStatusType::TYPE_POTENTIAL,
                ActionStatusType::TYPE_ACTIVE
            ]
        ];

        $types = [
            'actionStatusTypes' => Connection::PARAM_STR_ARRAY
        ];

        if ($agentIdentifiers) {
            $parameters['agentIdentifiers'] = $agentIdentifiers;
            $types['agentIdentifiers'] = Connection::PARAM_STR_ARRAY;
            $sql .= ' AND agent IN (:agentIdentifiers)';
        }

        $rawDataSet = $this->getDatabaseConnection()->executeQuery(
            $sql,
            $parameters,
            $types
        )->fetchAllAssociative();

        return (int) $rawDataSet[0]['COUNT(*)'];
    }

    final function countPastDue(?array $agentIdentifiers = null): int
    {
        $sql = 'SELECT COUNT(*) FROM ' . self::TABLE_NAME . '
            WHERE
                actionstatus IN (:actionStatusTypes)
            AND scheduledTime < NOW()
            AND TO_DAYS(scheduledTime) <> TO_DAYS(NOW())';

        $parameters = [
            'actionStatusTypes' => [
                ActionStatusType::TYPE_POTENTIAL,
                ActionStatusType::TYPE_ACTIVE
            ]
        ];

        $types = [
            'actionStatusTypes' => Connection::PARAM_STR_ARRAY
        ];

        if ($agentIdentifiers) {
            $parameters['agentIdentifiers'] = $agentIdentifiers;
            $types['agentIdentifiers'] = Connection::PARAM_STR_ARRAY;
            $sql .= ' AND agent IN (:agentIdentifiers)';
        }

        $rawDataSet = $this->getDatabaseConnection()->executeQuery(
            $sql,
            $parameters,
            $types
        )->fetchAllAssociative();

        return (int) $rawDataSet[0]['COUNT(*)'];
    }

    final function countUpcoming(\DateInterval $upcomingInterval, ?array $agentIdentifiers = null): int
    {
        $now = ScheduledTime::now();
        $referenceDate = $now->add($upcomingInterval);

        $sql = 'SELECT COUNT(*) FROM ' . self::TABLE_NAME . '
            WHERE
                actionstatus IN (:actionStatusTypes)
            AND scheduledTime <= :referenceDate
            AND scheduledTime > NOW()
            AND TO_DAYS(scheduledTime) <> TO_DAYS(NOW())';

        $parameters = [
            'referenceDate' => $referenceDate,
            'actionStatusTypes' => [
                ActionStatusType::TYPE_POTENTIAL,
                ActionStatusType::TYPE_ACTIVE
            ]
        ];

            $types = [
            'referenceDate' => Types::DATETIME_IMMUTABLE,
            'actionStatusTypes' => Connection::PARAM_STR_ARRAY
        ];

        if ($agentIdentifiers) {
            $parameters['agentIdentifiers'] = $agentIdentifiers;
            $types['agentIdentifiers'] = Connection::PARAM_STR_ARRAY;
            $sql .= ' AND agent IN (:agentIdentifiers)';
        }

        $rawDataSet = $this->getDatabaseConnection()->executeQuery(
            $sql,
            $parameters,
            $types
        )->fetchAllAssociative();

        return (int) $rawDataSet[0]['COUNT(*)'];
    }

    final public function findPotentialTasksOfClassForObject(TaskClassName $taskClassName, NodeAddress $object): Tasks
    {
        $rawDataSet = $this->getDatabaseConnection()->executeQuery(
            'SELECT * FROM ' . self::TABLE_NAME . '
 WHERE classname = :taskClassName
    AND object = :object
    AND actionstatus = :actionStatusType',
            [
                'taskClassName' => $taskClassName,
                'object' => $object,
                'actionStatusType' => ActionStatusType::TYPE_POTENTIAL
            ]
        )->fetchAllAssociative();

        $tasks = $this->createTasksFromTableRows($rawDataSet);
        return $tasks;
    }

    final public function findActiveOrPotentialTasksForObject(NodeAddress $object, ?TaskClassName $taskClassName = null): Tasks
    {
        $query = 'SELECT * FROM ' . self::TABLE_NAME . '
                WHERE object = :object
                AND actionstatus IN (:actionStatusTypes)';
        $params = [
            'object' => $object,
            'actionStatusTypes' => [
                ActionStatusType::TYPE_POTENTIAL,
                ActionStatusType::TYPE_ACTIVE
            ]
        ];
        if ($taskClassName) {
            $query .= '
                AND classname = :taskClassName';
            $params['taskClassName'] = $taskClassName->getValue();
        }

        $tableRows = $this->getDatabaseConnection()->executeQuery(
            $query,
            $params,
            [
                'actionStatusTypes' => Connection::PARAM_STR_ARRAY
            ]
        )->fetchAllAssociative();

        return $this->createTasksFromTableRows($tableRows);
    }

    final public function scheduleTask(ScheduleTask $command): void
    {
        $this->getDatabaseConnection()->insert(
            self::TABLE_NAME,
            [
                'identifier' => (string)$command->getIdentifier(),
                'classname' => (string)$command->getClassName(),
                'properties' => $command->getProperties(),
                'scheduledtime' => $command->getScheduledTime(),
                'actionstatus' => ActionStatusType::potential(),
                'agent' => $command->getAgent(),
                'object' => $command->getObject() ? json_encode($command->getObject()) : null,
                'target' => $command->getTarget()
            ],
            [
                'scheduledtime' => Types::DATETIME_IMMUTABLE,
                'properties' => Types::JSON
            ]
        );
    }

    final public function rescheduleTask(TaskIdentifier $taskIdentifier, \DateTimeImmutable $scheduledTime): void
    {
        $this->getDatabaseConnection()->update(
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

    final public function reassignTask(TaskIdentifier $taskIdentifier, Agent $agent): void
    {
        $this->getDatabaseConnection()->update(
            self::TABLE_NAME,
            [
                'agent' => $agent,
            ],
            [
                'identifier' => $taskIdentifier,
            ]
        );
    }

    final public function setTaskObject(TaskIdentifier $taskIdentifier, ?NodeAddress $object): void
    {
        $this->getDatabaseConnection()->update(
            self::TABLE_NAME,
            [
                'object' => $object ? json_encode($object) : null,
            ],
            [
                'identifier' => $taskIdentifier,
            ]
        );
    }

    final public function setTaskTarget(TaskIdentifier $taskIdentifier, ?UriInterface $target): void
    {
        $this->getDatabaseConnection()->update(
            self::TABLE_NAME,
            [
                'target' => $target,
            ],
            [
                'identifier' => $taskIdentifier,
            ]
        );
    }

    final public function setTaskProperties(TaskIdentifier $taskIdentifier, array $properties): void
    {
        $this->getDatabaseConnection()->update(
            self::TABLE_NAME,
            [
                'properties' => $properties,
            ],
            [
                'identifier' => $taskIdentifier,
            ],
            [
                'properties' => Type::JSON
            ]
        );
    }

    final public function cancelTask(TaskIdentifier $taskIdentifier): void
    {
        $this->getDatabaseConnection()->executeQuery('DELETE FROM ' . self::TABLE_NAME . ' WHERE identifier = :identifier', [
            'identifier' => (string) $taskIdentifier
        ]);
    }

    final public function updateTaskActionStatus(TaskIdentifier $taskIdentifier, ActionStatusType $actionStatus): void
    {
        $this->getDatabaseConnection()->update(
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
    public function emitTaskActionStatusUpdated(TaskIdentifier $taskIdentifier, ActionStatusType $actionStatus = null)
    {
    }

    /**
     * @param array<int,mixed> $tableRows
     */
    private function createTasksFromTableRows(array $tableRows): Tasks
    {
        return new Tasks(array_map(function (array $tableRow): TaskInterface {
            return $this->createTaskFromTableRow($tableRow);
        }, $tableRows));
    }

    /**
     * @param array<string,mixed> $tableRow
     */
    private function createTaskFromTableRow(array $tableRow): TaskInterface
    {
        $className = TaskClassName::createFromString($tableRow['classname']);
        $factory = $this->resolveFactory($className);
        $agent = $this->agentRepository->findByString($tableRow['agent']);
        if (!$agent) {
            throw AgentDoesNotExist::althoughExpectedForIdentifier($tableRow['agent']);
        }

        $object = null;
        if (isset($tableRow['object']) && !empty($tableRow['object'])) {
            $object = NodeAddress::createFromArray(json_decode($tableRow['object'], true));
        }

        return $factory->createFromRawData(
            new TaskIdentifier($tableRow['identifier']),
            $className,
            json_decode($tableRow['properties'], true),
            ScheduledTime::createFromDatabaseValue($tableRow['scheduledtime']),
            ActionStatusType::createFromString($tableRow['actionstatus']),
            $agent,
            $object,
            isset($tableRow['target'])
                ? new Uri($tableRow['target'])
                : null
        );
    }

    private function resolveFactory(TaskClassName $className): TaskFactoryInterface
    {
        return isset($this->factoryMapping[(string) $className])
            ? new $this->factoryMapping[(string) $className]()
            : new GenericTaskFactory();
    }

    private function getDatabaseConnection(): Connection
    {
        return $this->databaseClient->getConnection();
    }
}
