<?php declare(strict_types=1);
namespace Sitegeist\Bitzer\Domain\Task;

use Neos\Eel\ProtectedContextAwareInterface;
use Neos\Flow\Annotations as Flow;

/**
 * The constraint check result registry
 * Collects exceptions that otherwise would have been
 *
 * @Flow\Proxy(false)
 */
final class ConstraintCheckResult implements ProtectedContextAwareInterface
{
    /**
     * The registry for failed constraint checks
     * @var array<string,\DomainException>
     */
    private array $failedChecks = [];

    /**
     * The registry for message arguments for the failed constraint checks
     * @var array<string,array<mixed,mixed>>
     */
    private array $messageArguments = [];

    public function registerFailedCheck(string $path, \DomainException $failedConstraintCheck, array $messageArguments = []): void
    {
        $this->failedChecks[$path] = $failedConstraintCheck;
        $this->messageArguments[$path] = $messageArguments;
    }

    public function getException(string $path): ?\DomainException
    {
        return $this->failedChecks[$path] ?? null;
    }

    public function getCode(string $path): ?int
    {
        if (!isset($this->failedChecks[$path])) {
            return null;
        }

        return $this->failedChecks[$path]->getCode();
    }

    public function getMessage(string $path): ?string
    {
        if (!isset($this->failedChecks[$path])) {
            return null;
        }

        return $this->failedChecks[$path]->getMessage();
    }

    public function getMessageArguments(string $path): ?array
    {
        if (!isset($this->messageArguments[$path])) {
            return null;
        }

        return $this->messageArguments[$path];
    }

    public function hasFailedAtPath(string $path): bool
    {
        return isset($this->failedChecks[$path]);
    }

    public function hasFailed(): bool
    {
        return !$this->hasSucceeded();
    }

    public function hasSucceeded(): bool
    {
        return empty($this->failedChecks);
    }

    /**
     * All methods are considered safe
     *
     * @param string $methodName
     * @return boolean
     */
    public function allowsCallOfMethod($methodName): bool
    {
        return true;
    }
}
