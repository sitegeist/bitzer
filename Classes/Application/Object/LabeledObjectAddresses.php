<?php

declare(strict_types=1);

namespace Sitegeist\Bitzer\Application\Object;

use Neos\Flow\Annotations as Flow;

/**
 * The labeled object address collection
 *
 * @implements \IteratorAggregate<int,LabeledObjectAddress>
 */
#[Flow\Proxy(false)]
final readonly class LabeledObjectAddresses implements \IteratorAggregate, \Countable
{
    /**
     * @var array<int,LabeledObjectAddress>
     */
    private array $items;

    public function __construct(LabeledObjectAddress ...$items)
    {
        $this->items = array_values($items);
    }

    /**
     * @return \Traversable<int,LabeledObjectAddress>
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
