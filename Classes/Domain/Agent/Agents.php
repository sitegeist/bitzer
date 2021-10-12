<?php declare(strict_types=1);
namespace Sitegeist\Bitzer\Domain\Agent;

use Neos\Flow\Annotations as Flow;

/**
 * The agent domain entity collection
 *
 * @Flow\Proxy(false)
 * @implements \IteratorAggregate<int,Agent>
 */
final class Agents implements \IteratorAggregate, \Countable
{
    /**
     * @var array<int,Agent>
     */
    private array $agents;

    /**
     * @param array<int,mixed> $items
     */
    public function __construct(array $items)
    {
        foreach ($items as $item) {
            if (!$item instanceof Agent) {
                throw new \InvalidArgumentException(self::class . ' can only consist of ' . Agent::class);
            }
        }
        $this->agents = $items;
    }

    /**
     * @return array<int,string>
     */
    public function getIdentifiers(): array
    {
        return array_map(function (Agent $agent): string {
            return $agent->getCombinedIdentifier();
        }, $this->agents);
    }

    /**
     * @return \ArrayIterator<int,Agent>
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
