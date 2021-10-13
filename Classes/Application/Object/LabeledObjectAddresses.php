<?php declare(strict_types=1);
namespace Sitegeist\Bitzer\Application\Object;

use Neos\Flow\Annotations as Flow;

/**
 * The labeled object address collection
 *
 * @Flow\Proxy(false)
 * @implements \IteratorAggregate<int,LabeledObjectAddress>
 */
final class LabeledObjectAddresses implements \IteratorAggregate, \Countable
{
    /**
     * @var array<int,LabeledObjectAddress>
     */
    private array $agents;

    /**
     * @param array<int,mixed> $items
     */
    public function __construct(array $items)
    {
        foreach ($items as $item) {
            if (!$item instanceof LabeledObjectAddress) {
                throw new \InvalidArgumentException(self::class . ' can only consist of ' . LabeledObjectAddress::class);
            }
        }
        $this->agents = $items;
    }

    /**
     * @return \ArrayIterator<int,LabeledObjectAddress>|LabeledObjectAddress[]
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->agents);
    }

    public function count(): int
    {
        return count($this->agents);
    }
}
