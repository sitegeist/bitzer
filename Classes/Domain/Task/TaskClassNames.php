<?php declare(strict_types=1);
namespace Sitegeist\Bitzer\Domain\Task;

use Neos\Flow\Annotations as Flow;

/**
 * The task class name domain entity collection
 *
 * @Flow\Proxy(false)
 * @implements \IteratorAggregate<int,TaskClassName>
 */
final class TaskClassNames implements \IteratorAggregate, \Countable
{
    /**
     * @var array<int,TaskClassName>
     */
    private array $taskClassNames;

    /**
     * @param array<int,mixed> $items
     */
    public function __construct(array $items)
    {
        foreach ($items as $item) {
            if (!$item instanceof TaskClassName) {
                throw new \InvalidArgumentException(self::class . ' can only consist of ' . TaskClassName::class);
            }
        }
        $this->taskClassNames = $items;
    }

    /**
     * @return \ArrayIterator<int,TaskClassName>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->taskClassNames);
    }

    public function count(): int
    {
        return count($this->taskClassNames);
    }
}
