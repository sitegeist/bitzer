<?php

declare(strict_types=1);

namespace Sitegeist\Bitzer\Domain\Task;

/**
 * The action status type value object according to https://schema.org/ActionStatusType
 */
enum ActionStatusType: string implements \JsonSerializable
{
    case TYPE_ACTIVE = 'https://schema.org/ActiveActionStatus';
    case TYPE_COMPLETED = 'https://schema.org/CompletedActionStatus';
    case TYPE_FAILED = 'https://schema.org/FailedActionStatus';
    case TYPE_POTENTIAL = 'https://schema.org/PotentialActionStatus';

    public function jsonSerialize(): string
    {
        return $this->value;
    }
}
