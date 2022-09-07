<?php
declare(strict_types=1);
namespace Sitegeist\Bitzer\Domain\Task;

use Neos\ContentRepository\Domain\Projection\Content\TraversableNodeInterface;
use Psr\Http\Message\UriInterface;
use Sitegeist\Bitzer\Domain\Agent\Agent;

/**
 * The interface to be implemented by tasks, according to https://schema.org/ScheduleAction
 */
interface TaskInterface
{
    /**
     * The short type to resolve the class name
     *
     * @return string
     */
    public static function getShortType(): string;

    public function getIdentifier(): TaskIdentifier;

    /**
     * The image describing the task. Must be a FontAwesome icon identifier available to the Neos UI.
     *
     * @return string
     */
    public function getImage(): string;

    /**
     * A description of the task.
     *
     * @return string
     */
    public function getDescription(): string;

    /**
     * The time the object is scheduled to.
     *
     * @return \DateTimeImmutable
     */
    public function getScheduledTime(): \DateTimeImmutable;

    /**
     * Indicates the current disposition of the Action.
     *
     * @return ActionStatusType
     */
    public function getActionStatus(): ActionStatusType;

    /**
     * The direct performer or driver of the action (animate or inanimate). e.g. John wrote a book.
     * In our case, as tasks are assigned to user groups, this is a Flow policy role identifier.
     *
     * @return Agent
     */
    public function getAgent(): Agent;

    /**
     * The object upon which the action is carried out, whose state is kept intact or changed.
     * Also known as the semantic roles patient, affected or undergoer (which change their state) or theme (which doesn't).
     *
     * For now, we expect that only nodes are affected by tasks, if at all.
     *
     * @return TraversableNodeInterface|null
     */
    public function getObject(): ?TraversableNodeInterface;

    /**
     * Indicates a target EntryPoint for an Action.
     *
     * In our case this is the URI for the next action to be done within this task.
     *
     * @return UriInterface|null
     */
    public function getTarget(): ?UriInterface;

    /**
     * Returns custom, arbitrary properties of a task.
     *
     * @return array
     */
    public function getProperties(): array;
}
