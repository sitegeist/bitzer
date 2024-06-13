<?php

declare(strict_types=1);

namespace Sitegeist\Bitzer\Domain\Agent;

enum AgentType: string implements \JsonSerializable
{
    case TYPE_ROLE = 'role';
    case TYPE_USER = 'user';

    public function jsonSerialize(): string
    {
        return $this->value;
    }
}
