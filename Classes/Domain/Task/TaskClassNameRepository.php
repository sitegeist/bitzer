<?php
declare(strict_types=1);
namespace Sitegeist\Bitzer\Domain\Task;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Reflection\ReflectionService;

/**
 * The task class name repository
 * @Flow\Scope("singleton")
 */
final class TaskClassNameRepository
{
    /**
     * @Flow\Inject
     * @var ReflectionService
     */
    protected $reflectionService;

    /**
     * @return array|TaskClassName[]
     */
    public function findAll(): array
    {
        $taskClassNames = [];
        $implementationClassNames = $this->reflectionService->getAllImplementationClassNamesForInterface(TaskInterface::class);
        foreach ($implementationClassNames as $implementationClassName) {
            $taskClassNames[] = TaskClassName::createFromString($implementationClassName);
        }

        return $taskClassNames;
    }
}
