<?php declare(strict_types=1);
namespace Sitegeist\Bitzer\Presentation;

use Neos\Flow\Annotations as Flow;
use Psr\Http\Message\UriInterface;

/**
 * The widget implementation
 * @Flow\Proxy(false)
 */
final class Widget implements WidgetInterface
{
    private string $icon;

    private UriInterface $uri;

    private string $title;

    private string $description;

    private ?string $footer;

    public function __construct(string $icon, UriInterface $uri, string $title, string $description, ?string $footer)
    {
        $this->icon = $icon;
        $this->uri = $uri;
        $this->title = $title;
        $this->description = $description;
        $this->footer = $footer;
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getFooter(): ?string
    {
        return $this->footer;
    }
}
