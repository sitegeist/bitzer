<?php
declare(strict_types=1);
namespace Sitegeist\Bitzer\Domain\Task;

use Neos\Flow\Annotations as Flow;

/**
 * The task class name value object
 * @Flow\Proxy(false)
 */
final class TaskClassName
{
    /**
     * @var string
     */
    private $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function fromString(string $value): TaskClassName
    {
        if (!class_exists($value)) {
            throw new ClassNameIsUnavailable('Given task class name "' . $value . '" is not available in this installation.', 1567428115);
        }
        if (!in_array(TaskInterface::class, class_implements($value))) {
            throw new ClassNameDefinesNoTask('Given class name "' . $value . '" does not define a task implementation.', 1567428237);
        }

        return new static($value);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
