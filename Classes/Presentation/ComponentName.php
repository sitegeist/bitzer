<?php
declare(strict_types=1);
namespace Sitegeist\Bitzer\Presentation;

use Neos\Flow\Annotations as Flow;
use Sitegeist\Bitzer\Domain\Task\TaskClassName;

/**
 * The component name value object
 * @Flow\Proxy(false)
 */
final class ComponentName
{
    /**
     * @var string
     */
    private $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function fromTaskClassName(TaskClassName $taskClassName, string $prefix = ''): ComponentName
    {
        $namespaceSegments = explode('\\', (string)$taskClassName);
        $numberOfSegments = count($namespaceSegments);
        $componentNameSegments = [];

        $i = 0;
        while ($i < ($numberOfSegments - 1) && $namespaceSegments[$i] !== 'Domain') {
            $componentNameSegments[] = $namespaceSegments[$i];
            $i++;
        }
        $componentName = implode('.', $componentNameSegments);
        $componentName .= ':Application.' . $prefix . end($namespaceSegments);

        return new static($componentName);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
