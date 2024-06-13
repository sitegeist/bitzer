<?php

declare(strict_types=1);

namespace Sitegeist\Bitzer\Tests\Behaviour\Bootstrap;

/*
 * This file is part of the Sitegeist.Bitzer package.
 */

use Behat\Behat\Context\Context;
use Neos\Behat\Tests\Behat\FlowContextTrait;
use Neos\ContentRepository\Tests\Behavior\Features\Bootstrap\NodeOperationsTrait;
use Neos\Flow\Tests\Behavior\Features\Bootstrap\SecurityOperationsTrait;

require_once(__DIR__ . '/../../../../../Application/Neos.Behat/Tests/Behat/FlowContext.php');
require_once(__DIR__ . '/TaskOperationsTrait.php');
require_once(__DIR__ . '/AgentsTrait.php');
require_once(__DIR__ . '/ObjectsTrait.php');
require_once(__DIR__ . '/../../../../../Framework/Neos.Flow/Tests/Behavior/Features/Bootstrap/IsolatedBehatStepsTrait.php');
require_once(__DIR__ . '/../../../../../Framework/Neos.Flow/Tests/Behavior/Features/Bootstrap/SecurityOperationsTrait.php');
require_once(__DIR__ . '/../../../../../Application/Neos.ContentRepository/Tests/Behavior/Features/Bootstrap/NodeOperationsTrait.php');

/**
 * Features context
 */
class FeatureContext implements Context
{
    use FlowContextTrait;
    use TaskOperationsTrait;
    use AgentsTrait;
    use ObjectsTrait;
    use SecurityOperationsTrait;
    use NodeOperationsTrait;

    /**
     * @var bool
     */
    private $isolated;

    /**
     * Initializes the context
     *
     * @param array $parameters Context parameters (configured through behat.yml)
     */
    public function __construct(array $parameters)
    {
        $this->initializeFlow();
        $this->setupSecurity();
        $this->setupTaskOperations();
        $this->isolated = false;
        putenv('FLOW_REWRITEURLS=1'); // we want to test URI generation
    }
}
