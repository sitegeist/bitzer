<?php

declare(strict_types=1);

namespace Sitegeist\Bitzer\Tests\Unit\Presentation;

use PHPUnit\Framework\TestCase;
use Sitegeist\Bitzer\Domain\Task\TaskClassName;
use Sitegeist\Bitzer\Presentation\ComponentName;
use Sitegeist\Bitzer\Tests\Unit\Presentation\Fixtures\DoSomethingElseTask;
use Sitegeist\Bitzer\Tests\Unit\Presentation\Fixtures\DoSomethingTask;

/**
 * Test cases for the component name value object
 */
class ComponentNameTest extends TestCase
{
    /**
     * @return array<int,array<mixed>>
     */
    public function classNameProvider(): array
    {
        return [
            [TaskClassName::createFromString(DoSomethingTask::class), '', 'Sitegeist.Bitzer.Tests.Unit.Presentation.Fixtures:Application.DoSomethingTask'],
            [TaskClassName::createFromString(DoSomethingTask::class), 'Prefix', 'Sitegeist.Bitzer.Tests.Unit.Presentation.Fixtures:Application.PrefixDoSomethingTask'],
            [TaskClassName::createFromString(DoSomethingElseTask::class), '', 'Sitegeist.Bitzer.Tests.Unit.Presentation.Fixtures:Application.DoSomethingElseTask'],
            [TaskClassName::createFromString(DoSomethingElseTask::class), 'Prefix', 'Sitegeist.Bitzer.Tests.Unit.Presentation.Fixtures:Application.PrefixDoSomethingElseTask']
        ];
    }

    /**
     * @dataProvider classNameProvider
     */
    public static function testFromTaskClassNameReturnsCorrectComponentName(TaskClassName $taskClassName, string $prefix, string $expectedComponentName): void
    {
        self::assertSame($expectedComponentName, (string) ComponentName::fromTaskClassName($taskClassName, $prefix));
    }
}
