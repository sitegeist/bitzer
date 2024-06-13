<?php

declare(strict_types=1);

namespace Sitegeist\Bitzer\Domain\Task;

use Neos\Flow\Annotations as Flow;

/**
 * The task class name domain entity collection
 *
 * @implements \IteratorAggregate<int,TaskClassName>
 */
#[Flow\Proxy(false)]
final class TaskClassNames implements \IteratorAggregate, \Countable
{
    /**
     * @var array<int,TaskClassName>
     */
    private array $items;

    public function __construct(TaskClassName ...$items)
    {
        $this->items = array_values($items);
    }

    /**
     * @return \Traversable<int,TaskClassName>
     */
    public function getIterator(): \Traversable
    {
        yield from $this->items;
    }

    public function count(): int
    {
        return count($this->items);
    }
}
