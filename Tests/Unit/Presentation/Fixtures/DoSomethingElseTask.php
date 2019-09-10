<?php
namespace My\Package\Task\DoSomethingElse;

use Neos\ContentRepository\Domain\Model\NodeInterface;
use Psr\Http\Message\UriInterface;
use Sitegeist\Bitzer\Domain\Task\ActionStatusType;
use Sitegeist\Bitzer\Domain\Task\TaskIdentifier;
use Sitegeist\Bitzer\Domain\Task\TaskInterface;

/**
 * Another fixture dummy task
 */
final class DoSomethingElseTask implements TaskInterface
{
    /**
     * The short type to resolve the class name
     *
     * @return string
     */
    public static function getShortType(): string
    {
        // TODO: Implement getShortType() method.
    }

    public function getIdentifier(): TaskIdentifier
    {
        // TODO: Implement getIdentifier() method.
    }

    /**
     * The image describing the task. Must be a FontAwesome icon identifier available to the Neos UI.
     *
     * @return string
     */
    public function getImage(): string
    {
        // TODO: Implement getImage() method.
    }

    /**
     * A description of the task.
     *
     * @return string
     */
    public function getDescription(): string
    {
        // TODO: Implement getDescription() method.
    }

    /**
     * The time the object is scheduled to.
     *
     * @return \DateTimeImmutable
     */
    public function getScheduledTime(): \DateTimeImmutable
    {
        // TODO: Implement getScheduledTime() method.
    }

    /**
     * Indicates the current disposition of the Action.
     *
     * @return ActionStatusType
     */
    public function getActionStatus(): ActionStatusType
    {
        // TODO: Implement getActionStatus() method.
    }

    /**
     * The direct performer or driver of the action (animate or inanimate). e.g. John wrote a book.
     * In our case, as tasks are assigned to user groups, this is a Flow policy role identifier.
     *
     * @return string
     */
    public function getAgent(): string
    {
        // TODO: Implement getAgent() method.
    }

    /**
     * The object upon which the action is carried out, whose state is kept intact or changed.
     * Also known as the semantic roles patient, affected or undergoer (which change their state) or theme (which doesn't).
     *
     * For now, we expect that only nodes are affected by tasks, if at all.
     *
     * @return NodeInterface|null
     */
    public function getObject(): ?NodeInterface
    {
        // TODO: Implement getObject() method.
    }

    /**
     * Indicates a target EntryPoint for an Action.
     *
     * In our case this is the URI for the next action to be done within this task.
     *
     * @return UriInterface|null
     */
    public function getTarget(): ?UriInterface
    {
        // TODO: Implement getTarget() method.
    }

    /**
     * Returns custom, arbitrary properties of a task.
     *
     * @return array
     */
    public function getProperties(): array
    {
        // TODO: Implement getProperties() method.
    }
}
