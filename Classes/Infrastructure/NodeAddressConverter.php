<?php
namespace Sitegeist\Bitzer\Infrastructure;

/*
 * This file is part of the Sitegeist.Bitzer package.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Property\PropertyMappingConfigurationInterface;
use Neos\Flow\Property\TypeConverter\AbstractTypeConverter;
use Sitegeist\Bitzer\Domain\Task\NodeAddress;

/**
 * A type converter from string to node address
 */
class NodeAddressConverter extends AbstractTypeConverter
{
    /**
     * The source types this converter can convert.
     *
     * @var array<string>
     * @api
     */
    protected $sourceTypes = ['string'];

    /**
     * The target type this converter can convert to.
     *
     * @var string
     * @api
     */
    protected $targetType = NodeAddress::class;

    /**
     * The priority for this converter.
     *
     * @var integer
     * @api
     */
    protected $priority = 10;

    public function convertFrom(
        $source,
        $targetType,
        array $convertedChildProperties = [],
        PropertyMappingConfigurationInterface $configuration = null
    ) {
        return NodeAddress::fromJsonString($source);
    }
}
