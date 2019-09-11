<?php
declare(strict_types=1);

/*
 * This file is part of the Sitegeist.Bitzer package.
 */

require_once(__DIR__ . '/../../../../../Application/Neos.Behat/Tests/Behat/FlowContext.php');
require_once(__DIR__ . '/TaskOperationsTrait.php');
require_once(__DIR__ . '/AgentsTrait.php');
require_once(__DIR__ . '/ObjectsTrait.php');
require_once(__DIR__ . '/../../../../../Framework/Neos.Flow/Tests/Behavior/Features/Bootstrap/IsolatedBehatStepsTrait.php');
require_once(__DIR__ . '/../../../../../Framework/Neos.Flow/Tests/Behavior/Features/Bootstrap/SecurityOperationsTrait.php');
require_once(__DIR__ . '/../../../../../Application/Neos.ContentRepository/Tests/Behavior/Features/Bootstrap/NodeOperationsTrait.php');

use Behat\Behat\Context\BehatContext;
use Neos\Behat\Tests\Behat\FlowContext;
use Neos\ContentRepository\Tests\Behavior\Features\Bootstrap\NodeOperationsTrait;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Flow\Tests\Behavior\Features\Bootstrap\SecurityOperationsTrait;

/**
 * Features context
 */
class FeatureContext extends BehatContext
{
    use TaskOperationsTrait;
    use AgentsTrait;
    use ObjectsTrait;
    use SecurityOperationsTrait;
    use NodeOperationsTrait;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

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
        $this->useContext('flow', new FlowContext($parameters));
        /** @var FlowContext $flowContext */
        $flowContext = $this->getSubcontext('flow');
        $this->objectManager = $flowContext->getObjectManager();

        $this->setupSecurity();
        $this->setupTaskOperations();
        $this->isolated = false;
        putenv('FLOW_REWRITEURLS=1'); // we want to test URI generation
    }

    protected function getObjectManager(): ObjectManagerInterface
    {
        return $this->objectManager;
    }
}
