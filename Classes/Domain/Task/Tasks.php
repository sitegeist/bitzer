<?php

declare(strict_types=1);

namespace Sitegeist\Bitzer\Domain\Task;

use Neos\Flow\Annotations as Flow;

/**
 * The task domain entity collection
 *
 * @implements \IteratorAggregate<int,TaskInterface>
 */
#[Flow\Proxy(false)]
final class Tasks implements \IteratorAggregate, \Countable
{
    /**
     * @var array<int,TaskInterface>
     */
    private array $items;

    public function __construct(TaskInterface ...$items)
    {
        $this->items = array_values($items);
    }

    /**
     * @return \Traversable<int,TaskInterface>
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
