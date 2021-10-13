<?php declare(strict_types=1);
namespace Sitegeist\Bitzer\Presentation;

use GuzzleHttp\Psr7\Uri;
use Neos\Eel\ProtectedContextAwareInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\I18n\Translator;
use Neos\Flow\Mvc\Routing\UriBuilder;
use Neos\Fusion\FusionObjects\AbstractFusionObject;

/**
 * The widget factory
 * @Flow\Scope("singleton")
 */
final class WidgetFactory implements ProtectedContextAwareInterface
{
    private Translator $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    public function forSchedule(AbstractFusionObject $component): WidgetInterface
    {
        $uriBuilder = clone $component->getRuntime()->getControllerContext()->getUriBuilder();

        return new Widget(
            'fas fa-clipboard-list',
            $this->getActionUri($uriBuilder, 'schedule'),
            $this->getLabel('schedule.label'),
            $this->getLabel('schedule.description'),
            $this->getLabel('actions.prepareTask.label')
        );
    }

    public function forMySchedule(AbstractFusionObject $component): WidgetInterface
    {
        $uriBuilder = clone $component->getRuntime()->getControllerContext()->getUriBuilder();

        return new Widget(
            'fas fa-clipboard-list',
            $this->getActionUri($uriBuilder, 'mySchedule'),
            $this->getLabel('mySchedule.label'),
            $this->getLabel('mySchedule.description'),
            null
        );
    }

    private function getActionUri(UriBuilder $uriBuilder, string $actionName, array $arguments = []): Uri
    {
        return new Uri($uriBuilder->uriFor(
            $actionName,
            $arguments,
            'Bitzer',
            'Sitegeist.Bitzer',
            'Application'
        ));
    }

    private function getLabel(string $id, array $arguments = []): string
    {
        return $this->translator->translateById(
            $id,
            $arguments,
            null,
            null,
            'Module.Bitzer',
            'Sitegeist.Bitzer'
        ) ?: $id;
    }

    public function allowsCallOfMethod($methodName): bool
    {
        return true;
    }
}
