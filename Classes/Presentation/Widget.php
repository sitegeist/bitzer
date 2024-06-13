<?php

declare(strict_types=1);

namespace Sitegeist\Bitzer\Presentation;

use Neos\Flow\Annotations as Flow;
use Psr\Http\Message\UriInterface;

/**
 * The widget implementation
 */
#[Flow\Proxy(false)]
final readonly class Widget
{
    public function __construct(
        public string $icon,
        public UriInterface $uri,
        public string $title,
        public string $description,
        public ?string $footer
    ) {
    }
}
