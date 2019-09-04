<?php
declare(strict_types=1);

namespace Sitegeist\Bitzer\Domain\Task;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\ContentRepository\Domain\Service\ContextFactoryInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Neos\Domain\Service\ContentDimensionPresetSourceInterface;
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

    final public function addTask(CreateTask $command): void
    {
        $this->getDatabaseConnection()->executeQuery('INSERT INTO ' . self::TABLE_NAME . ' VALUES (
        :identifier, :className, :description, :scheduledTime, :actionStatus, :agent, :object, :target)',
            [
                'identifier' => (string)$command->getIdentifier(),
                'classname' => (string)$command->getClassName(),
                'description' => $command->getDescription(),
                'scheduledTime' => $command->getScheduledTime(),
                'actionStatus' => ActionStatusType::potential(),
                'agent' => $command->getAgent(),
                'object' => $command->getObject(),
                'target' => $command->getTarget()
            ],
            [
                'scheduledTime' => Type::DATE_IMMUTABLE
            ]
        );
    }

    private function createTaskFromRawData(array $rawData): TaskInterface
    {
        $className = TaskClassName::fromString($rawData['classname']);
        $factory = $this->resolveFactory($className);
        $object = null;
        if (isset($rawData['object']) && !empty($rawData['object'])) {
            $nodeAddress = NodeAddress::fromArray(json_decode($rawData['object'], true));
            $object = $this->resolveNode($nodeAddress);
        }

        return $factory->createFromRawData(
            new TaskIdentifier($rawData['identifier']),
            $className,
            $rawData['description'],
            \DateTimeImmutable::createFromFormat('c', $rawData['scheduledtime']),
            ActionStatusType::createFromString($rawData['actionstatus']),
            $rawData['agent'],
            $object,
            $rawData['target'] ?? null
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
