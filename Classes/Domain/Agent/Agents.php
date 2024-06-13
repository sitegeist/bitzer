<?php

declare(strict_types=1);

namespace Sitegeist\Bitzer\Domain\Agent;

use Neos\Flow\Annotations as Flow;

/**
 * The agent domain entity collection
 *
 * @implements \IteratorAggregate<int,Agent>
 */
#[Flow\Proxy(false)]
final class Agents implements \IteratorAggregate, \Countable
{
    /**
     * @var array<int,Agent>
     */
    private array $items;

    public function __construct(Agent ...$items)
    {
        $this->items = array_values($items);
    }

    /**
     * @return array<int,string>
     */
    public function getIdentifiers(): array
    {
        return array_map(function (Agent $agent): string {
            return $agent->identifier->toString();
        }, $this->items);
    }

    /**
     * @return \Traversable<int,Agent>
     */
    public function getIterator(): \Traversable
    {
        return yield from $this->items;
    }

    public function isEmpty(): bool
    {
        return $this->items === [];
    }

    public function count(): int
    {
        return count($this->items);
    }
}
