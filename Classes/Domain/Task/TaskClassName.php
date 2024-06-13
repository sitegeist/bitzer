<?php

declare(strict_types=1);

namespace Sitegeist\Bitzer\Domain\Task;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Reflection\ReflectionService;
use Sitegeist\Bitzer\Domain\Task\Exception\ClassNameDefinesNoTask;
use Sitegeist\Bitzer\Domain\Task\Exception\ClassNameIsUnavailable;
use Sitegeist\Bitzer\Domain\Task\Exception\ShortTypeDefinesNoTask;

/**
 * The task class name value object
 */
#[Flow\Proxy(false)]
final readonly class TaskClassName
{
    /**
     * @param class-string $value
     */
    public function __construct(
        public string $value
    ) {
        if (!class_exists($value)) {
            throw new ClassNameIsUnavailable('Given task class name "' . $value . '" is not available in this installation.', 1567428115);
        }
        if (!in_array(TaskInterface::class, class_implements($value))) {
            throw new ClassNameDefinesNoTask('Given class name "' . $value . '" does not define a task implementation.', 1567428237);
        }
    }

    public static function createFromString(string $value): TaskClassName
    {
        if (!class_exists($value)) {
            throw new ClassNameIsUnavailable('Given task class name "' . $value . '" is not available in this installation.', 1567428115);
        }
        return new self($value);
    }

    public static function createFromObject(object $object): TaskClassName
    {
        return new self(get_class($object));
    }

    public static function fromShortType(string $shortType, ReflectionService $reflectionService): TaskClassName
    {
        $classNames = $reflectionService->getAllImplementationClassNamesForInterface(TaskInterface::class);
        foreach ($classNames as $className) {
            if ($className::getShortType() === $shortType) {
                return new self($className);
            }
        }

        throw new ShortTypeDefinesNoTask('Given short type "' . $shortType . '" does not define a task implementation.', 1567507976);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
