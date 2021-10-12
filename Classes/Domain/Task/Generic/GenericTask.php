<?php declare(strict_types=1);
namespace Sitegeist\Bitzer\Domain\Task\Generic;

use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\ContentRepository\Domain\Projection\Content\TraversableNodeInterface;
use Neos\Flow\Annotations as Flow;
use Psr\Http\Message\UriInterface;
use Sitegeist\Bitzer\Domain\Task\ActionStatusType;
use Sitegeist\Bitzer\Domain\Task\TaskIdentifier;
use Sitegeist\Bitzer\Domain\Task\TaskInterface;
use Sitegeist\Bitzer\Domain\Agent\Agent;

/**
 * The generic task domain entity
 * @Flow\Proxy(false)
 */
final class GenericTask implements TaskInterface
{
    private TaskIdentifier $identifier;

    private array $properties;

    private \DateTimeImmutable $scheduledTime;

    private ActionStatusType $actionStatus;

    private Agent $agent;

    private ?NodeInterface $object;

    private ?UriInterface $target;

    public function __construct(
        TaskIdentifier $identifier,
        array $properties,
        \DateTimeImmutable $scheduledTime,
        ActionStatusType $actionStatus,
        Agent $agent,
        ?TraversableNodeInterface $object,
        ?UriInterface $target
    ) {
        $this->identifier = $identifier;
        $this->properties = $properties;
        $this->scheduledTime = $scheduledTime;
        $this->actionStatus = $actionStatus;
        $this->agent = $agent;
        $this->object = $object;
        $this->target = $target;
    }

    public static function getShortType(): string
    {
        return 'generic';
    }


    public function getIdentifier(): TaskIdentifier
    {
        return $this->identifier;
    }

    /**
     * The image describing the task. Must be a FontAwesome icon identifier available to the Neos UI.
     */
    public function getImage(): string
    {
        return 'clipboard';
    }

    /**
     * A description of the task.
     */
    public function getDescription(): string
    {
        return $this->properties['description'] ?? '';
    }

    /**
     * The time the object is scheduled to.
     */
    public function getScheduledTime(): \DateTimeImmutable
    {
        return $this->scheduledTime;
    }

    /**
     * Indicates the current disposition of the Action.
     */
    public function getActionStatus(): ActionStatusType
    {
        return $this->actionStatus;
    }

    /**
     * The direct performer or driver of the action (animate or inanimate). e.g. John wrote a book.
     * In our case, as tasks are assigned to user groups, this is a Flow policy role identifier.
     */
    public function getAgent(): Agent
    {
        return $this->agent;
    }

    /**
     * The object upon which the action is carried out, whose state is kept intact or changed.
     * Also known as the semantic roles patient, affected or undergoer (which change their state) or theme (which doesn't).
     *
     * For now, we expect that only nodes are affected by tasks, if at all.
     */
    public function getObject(): ?TraversableNodeInterface
    {
        return $this->object;
    }

    /**
     * Indicates a target EntryPoint for an Action.
     *
     * In our case this is the URI for the next action to be done within this task.
     */
    public function getTarget(): ?UriInterface
    {
        return $this->target;
    }

    public function getProperties(): array
    {
        return $this->properties;
    }
}
