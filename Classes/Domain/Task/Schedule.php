<?php
declare(strict_types=1);
namespace Sitegeist\Bitzer\Domain\Task;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\ContentRepository\Domain\Service\ContextFactoryInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Http\Uri;
use Neos\Neos\Domain\Service\ContentDimensionPresetSourceInterface;
use Sitegeist\Bitzer\Domain\Task\Command\ScheduleTask;
use Sitegeist\Bitzer\Domain\Task\Generic\GenericTaskFactory;
use Sitegeist\Bitzer\Infrastructure\DbalClient;

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
     * @var ContextFactoryInterface
     */
    protected $contentContextFactory;

    /**
     * @Flow\Inject
     * @var ContentDimensionPresetSourceInterface
     */
    protected $contentDimensionPresetSource;

    final public function findByIdentifier(TaskIdentifier $identifier): ?TaskInterface
    {
        $rawData = $this->getDatabaseConnection()->executeQuery(
            'SELECT * FROM ' . self::TABLE_NAME . '
 WHERE identifier = :identifier',
            [
                'identifier' => (string)$identifier
            ]
        )->fetch();

        if (!empty($rawData)) {
            return $this->createTaskFromRawData($rawData);
        }

        return null;
    }

    /**
     * @return array|TaskInterface[]
     * @throws \Doctrine\DBAL\DBALException
     */
    final public function findAll(): array
    {
        $rawDataSet = $this->getDatabaseConnection()->executeQuery(
            'SELECT * FROM ' . self::TABLE_NAME
        )->fetchAll();

        return $this->createTasksFromRawDataSet($rawDataSet);
    }
    /**
     * @return array|TaskInterface[]
     * @throws \Doctrine\DBAL\DBALException
     */
    final public function findAllOrdered(): array
    {
        $rawDataSet = $this->getDatabaseConnection()->executeQuery(
            'SELECT * FROM ' . self::TABLE_NAME . ' ORDER BY scheduledtime ASC'
        )->fetchAll();

        return $this->createTasksFromRawDataSet($rawDataSet);
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
        $referenceDate = $now->sub($upcomingInterval);

        $query = 'SELECT * FROM ' . self::TABLE_NAME . '
 WHERE scheduledtime >= :referenceDate
 AND actionstatus IN (:actionStatusTypes)';
        $parameters = [
            'referenceDate' => $referenceDate,
            'actionStatusTypes' => [
                ActionStatusType::TYPE_POTENTIAL,
                ActionStatusType::TYPE_ACTIVE
            ]
        ];
        $types = [
            'referenceDate' => Type::DATETIME_IMMUTABLE,
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
        )->fetchAll();
        $tasks = $this->createTasksFromRawDataSet($rawDataSet);

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
                'scheduledtime' => Type::DATETIME_IMMUTABLE,
                'properties' => Type::JSON
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
                'scheduledtime' => Type::DATETIME_IMMUTABLE
            ]
        );
    }

    final public function reassignTask(TaskIdentifier $taskIdentifier, string $agent): void
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
    }

    /**
     * @param array $rawDataSet
     * @return array|TaskInterface[]
     */
    private function createTasksFromRawDataSet(array $rawDataSet): array
    {
        $tasks = [];
        foreach ($rawDataSet as $rawData) {
            $tasks[] = $this->createTaskFromRawData($rawData);
        }

        return $tasks;
    }

    private function createTaskFromRawData(array $rawData): TaskInterface
    {
        $className = TaskClassName::createFromString($rawData['classname']);
        $factory = $this->resolveFactory($className);
        $object = null;
        if (isset($rawData['object']) && !empty($rawData['object'])) {
            $nodeAddress = NodeAddress::fromArray(json_decode($rawData['object'], true));
            $object = $this->resolveNode($nodeAddress);
        }

        return $factory->createFromRawData(
            new TaskIdentifier($rawData['identifier']),
            $className,
            json_decode($rawData['properties'], true),
            ScheduledTime::createFromDatabaseValue($rawData['scheduledtime']),
            ActionStatusType::createFromString($rawData['actionstatus']),
            $rawData['agent'],
            $object,
            isset($rawData['target']) ? new Uri($rawData['target']) : null
        );
    }

    private function resolveNode(NodeAddress $nodeAddress): ?NodeInterface
    {
        $presets = $this->contentDimensionPresetSource->getAllPresets();
        $contextDimensions = [];
        foreach ($nodeAddress->getDimensionSpacePoint()->getCoordinates() as $dimensionName => $dimensionValue) {
            $contextDimensions[$dimensionName] = $presets[$dimensionName]['presets'][$dimensionValue]['values'];
        }
        $contentContext = $this->contentContextFactory->create([
             'workspaceName' => $nodeAddress->getWorkspaceName(),
             'dimensions' => $contextDimensions,
             'targetDimensions' => $nodeAddress->getDimensionSpacePoint()->getCoordinates(),
             'invisibleContentShown' => true,
             'removedContentShown' => false,
             'inaccessibleContentShown' => true
        ]);

        return $contentContext->getNodeByIdentifier($nodeAddress['nodeAggregateIdentifier']);
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
