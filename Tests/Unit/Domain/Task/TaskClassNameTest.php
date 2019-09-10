<?php
namespace Sitegeist\Bitzer\Tests\Unit\Domain\Task;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Tests\UnitTestCase;
use Sitegeist\Bitzer\Domain\Task\Exception\ClassNameDefinesNoTask;
use Sitegeist\Bitzer\Domain\Task\Exception\ClassNameIsUnavailable;
use Sitegeist\Bitzer\Domain\Task\TaskClassName;
use Sitegeist\Bitzer\Tests\Unit\Domain\Task\Fixtures\InvalidTask;

/**
 * Test cases for the task class name value object
 */
class TaskClassNameTest extends UnitTestCase
{
    public function setUp()
    {
        parent::setUp();
        require_once ('Fixtures/InvalidTask.php');
    }

    /**
     * @test
     */
    public function fromStringThrowsCorrectExceptionForUnavailableClassName()
    {
        $correctExceptionThrown = false;
        try {
            $className = TaskClassName::createFromString('I\\Do\\Not\\Exist');
        } catch (ClassNameIsUnavailable $expectedException) {
            $correctExceptionThrown = true;
        }

        $this->assertSame(true, $correctExceptionThrown);
    }

    /**
     * @test
     */
    public function fromStringThrowsCorrectExceptionForNonTaskClassNames()
    {
        $correctExceptionThrown = false;
        try {
            $className = TaskClassName::createFromString(InvalidTask::class);
        } catch (ClassNameDefinesNoTask $expectedException) {
            $correctExceptionThrown = true;
        }

        $this->assertSame(true, $correctExceptionThrown);
    }
}
