<?php declare(strict_types=1);
namespace Sitegeist\Bitzer\Presentation;

use Neos\Flow\Annotations as Flow;
use Psr\Http\Message\UriInterface;

/**
 * The widget interface for usage in the index view
 * @Flow\Proxy(false)
 */
interface WidgetInterface
{
    public function getIcon(): string;

    public function getUri(): UriInterface;

    public function getTitle(): string;

    public function getDescription(): string;

    public function getFooter(): ?string;
}
