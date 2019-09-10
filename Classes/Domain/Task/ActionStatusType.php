<?php
declare(strict_types=1);
namespace Sitegeist\Bitzer\Domain\Task;

use Neos\Flow\Annotations as Flow;
use Sitegeist\Bitzer\Domain\Task\Exception\ActionStatusTypeIsInvalid;

/**
 * The action status type value object according to https://schema.org/ActionStatusType
 * @Flow\Proxy(false)
 */
final class ActionStatusType
{
    const TYPE_ACTIVE = 'https://schema.org/ActiveActionStatus';
    const TYPE_COMPLETED = 'https://schema.org/CompletedActionStatus';
    const TYPE_FAILED = 'https://schema.org/FailedActionStatus';
    const TYPE_POTENTIAL = 'https://schema.org/PotentialActionStatus';

    /**
     * @var string
     */
    protected $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function createFromString(string $value)
    {
        if ($value !== self::TYPE_ACTIVE && $value !== self::TYPE_COMPLETED && $value !== self::TYPE_FAILED && $value !== self::TYPE_POTENTIAL) {
            throw new ActionStatusTypeIsInvalid('Given action status "' . $value . '" is invalid, must be one of the predefined constants. See also https://schema.org/ActionStatusType', 1567430844);
        }

        return new static($value);
    }

    public static function active(): ActionStatusType
    {
        return new static(self::TYPE_ACTIVE);
    }

    public static function completed(): ActionStatusType
    {
        return new static(self::TYPE_COMPLETED);
    }

    public static function failed(): ActionStatusType
    {
        return new static(self::TYPE_FAILED);
    }

    public static function potential(): ActionStatusType
    {
        return new static(self::TYPE_POTENTIAL);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function equals(ActionStatusType $other): bool
    {
        return $this->value === $other->getValue();
    }
}
