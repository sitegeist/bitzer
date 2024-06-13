<?php

declare(strict_types=1);

namespace Sitegeist\Bitzer\Tests\Unit\Presentation\Fixtures;

use Neos\ContentRepository\Domain\Projection\Content\TraversableNodeInterface;
use Psr\Http\Message\UriInterface;
use Sitegeist\Bitzer\Domain\Agent\AgentIdentifier;
use Sitegeist\Bitzer\Domain\Agent\AgentType;
use Sitegeist\Bitzer\Domain\Task\ActionStatusType;
use Sitegeist\Bitzer\Domain\Task\TaskIdentifier;
use Sitegeist\Bitzer\Domain\Task\TaskInterface;
use Sitegeist\Bitzer\Domain\Agent\Agent;

/**
 * Another fixture dummy task
 */
final class DoSomethingElseTask implements TaskInterface
{
    /**
     * The short type to resolve the class name
     */
    public static function getShortType(): string
    {
        return 'do-something-else';
    }

    public function getIdentifier(): TaskIdentifier
    {
        return TaskIdentifier::create();
    }

    /**
     * The image describing the task. Must be a FontAwesome icon identifier available to the Neos UI.
     *
     * @return string
     */
    public function getImage(): string
    {
        return '';
    }

    /**
     * A description of the task.
     *
     * @return string
     */
    public function getDescription(): string
    {
        return '';
    }

    /**
     * The time the object is scheduled to.
     */
    public function getScheduledTime(): \DateTimeImmutable
    {
        return new \DateTimeImmutable();
    }

    /**
     * Indicates the current disposition of the Action.
     *
     * @return ActionStatusType
     */
    public function getActionStatus(): ActionStatusType
    {
        return ActionStatusType::TYPE_POTENTIAL;
    }

    /**
     * The direct performer or driver of the action (animate or inanimate). e.g. John wrote a book.
     * In our case, as tasks are assigned to user groups, this is a Flow policy role identifier.
     *
     * @return Agent
     */
    public function getAgent(): Agent
    {
        return new Agent(new AgentIdentifier(AgentType::TYPE_ROLE, 'Vendor.Site:Role'), 'Some agent');
    }

    /**
     * The object upon which the action is carried out, whose state is kept intact or changed.
     * Also known as the semantic roles patient, affected or undergoer (which change their state) or theme (which doesn't).
     *
     * For now, we expect that only nodes are affected by tasks, if at all.
     */
    public function getObject(): ?TraversableNodeInterface
    {
        return null;
    }

    /**
     * Indicates a target EntryPoint for an Action.
     *
     * In our case this is the URI for the next action to be done within this task.
     */
    public function getTarget(): ?UriInterface
    {
        return null;
    }

    /**
     * Returns custom, arbitrary properties of a task.
     *
     * @return array<string,mixed>
     */
    public function getProperties(): array
    {
        return [];
    }
}
