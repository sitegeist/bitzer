<?php declare(strict_types=1);
namespace Sitegeist\Bitzer\Domain\Task;

use Neos\Flow\Annotations as Flow;

/**
 * The task domain entity collection
 * @Flow\Proxy(false)
 * @implements \IteratorAggregate<int,TaskInterface>
 */
final class Tasks implements \IteratorAggregate, \Countable
{
    /**
     * @var array<int,TaskInterface>
     */
    private array $approvalAssignments;

    /**
     * @param array<int,mixed> $items
     */
    public function __construct(array $items)
    {
        foreach ($items as $item) {
            if (!$item instanceof TaskInterface) {
                throw new \InvalidArgumentException(self::class . ' can only consist of ' . TaskInterface::class);
            }
        }
        $this->approvalAssignments = $items;
    }

    /**
     * @return \ArrayIterator<int,TaskInterface>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->approvalAssignments);
    }

    public function count(): int
    {
        return count($this->approvalAssignments);
    }
}
