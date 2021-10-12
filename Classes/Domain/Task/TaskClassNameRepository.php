<?php declare(strict_types=1);
namespace Sitegeist\Bitzer\Domain\Task;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Reflection\ReflectionService;

/**
 * The task class name domain repository
 *
 * @Flow\Scope("singleton")
 */
final class TaskClassNameRepository
{
    private ReflectionService $reflectionService;

    public function __construct(ReflectionService $reflectionService)
    {
        $this->reflectionService = $reflectionService;
    }

    public function findAll(): TaskClassNames
    {
        $implementationClassNames = $this->reflectionService->getAllImplementationClassNamesForInterface(TaskInterface::class);

        return new TaskClassNames(array_map(function (string $implementationClassName): TaskClassName {
            return TaskClassName::createFromString($implementationClassName);
        }, $implementationClassNames));
    }
}
