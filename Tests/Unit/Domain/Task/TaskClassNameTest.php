<?php

namespace Sitegeist\Bitzer\Tests\Unit\Domain\Task;

use PHPUnit\Framework\TestCase;
use Sitegeist\Bitzer\Domain\Task\Exception\ClassNameDefinesNoTask;
use Sitegeist\Bitzer\Domain\Task\Exception\ClassNameIsUnavailable;
use Sitegeist\Bitzer\Domain\Task\TaskClassName;
use Sitegeist\Bitzer\Tests\Unit\Domain\Task\Fixtures\InvalidTask;

/**
 * Test cases for the task class name value object
 */
class TaskClassNameTest extends TestCase
{
    public function testFromStringThrowsCorrectExceptionForUnavailableClassName(): void
    {
        $correctExceptionThrown = false;
        try {
            TaskClassName::createFromString('I\\Do\\Not\\Exist');
        } catch (ClassNameIsUnavailable $expectedException) {
            $correctExceptionThrown = true;
        }

        self::assertSame(true, $correctExceptionThrown);
    }

    public function testFromStringThrowsCorrectExceptionForNonTaskClassNames(): void
    {
        $correctExceptionThrown = false;
        try {
            TaskClassName::createFromString(InvalidTask::class);
        } catch (ClassNameDefinesNoTask $expectedException) {
            $correctExceptionThrown = true;
        }

        self::assertSame(true, $correctExceptionThrown);
    }
}
