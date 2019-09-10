<?php
namespace Sitegeist\Bitzer\Tests\Unit\Domain\Task;

use My\Package\Domain\Task\DoSomething\DoSomethingTask;
use My\Package\Task\DoSomethingElse\DoSomethingElseTask;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Tests\UnitTestCase;
use Sitegeist\Bitzer\Domain\Task\TaskClassName;
use Sitegeist\Bitzer\Presentation\ComponentName;

/**
 * Test cases for the component name value object
 */
class ComponentNameTest extends UnitTestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function classNameProvider(): array
    {
        require_once ('Fixtures/DoSomethingTask.php');
        require_once ('Fixtures/DoSomethingElseTask.php');
        return [
            [TaskClassName::createFromString(DoSomethingTask::class), '', 'My.Package:Application.DoSomethingTask'],
            [TaskClassName::createFromString(DoSomethingTask::class), 'Prefix', 'My.Package:Application.PrefixDoSomethingTask'],
            [TaskClassName::createFromString(DoSomethingElseTask::class), '', 'My.Package.Task.DoSomethingElse:Application.DoSomethingElseTask'],
            [TaskClassName::createFromString(DoSomethingElseTask::class), 'Prefix', 'My.Package.Task.DoSomethingElse:Application.PrefixDoSomethingElseTask']
        ];
    }

    /**
     * @test
     * @dataProvider classNameProvider
     * @param TaskClassName $taskClassName
     * @param string $prefix
     * @param string $expectedComponentName
     */
    public function fromTaskClassNameReturnsCorrectComponentName(TaskClassName $taskClassName, string $prefix, string $expectedComponentName)
    {
        $this->assertSame($expectedComponentName, (string) ComponentName::fromTaskClassName($taskClassName, $prefix));
    }
}
