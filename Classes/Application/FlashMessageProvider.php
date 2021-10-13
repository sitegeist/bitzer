<?php declare(strict_types=1);
namespace Sitegeist\Bitzer\Application;

use Neos\Eel\ProtectedContextAwareInterface;
use Neos\Error\Messages\Message;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\ActionRequest;
use Neos\Flow\Mvc\FlashMessage\FlashMessageContainer;
use Neos\Flow\Mvc\FlashMessage\FlashMessageService;
use Neos\Fusion\FusionObjects\AbstractFusionObject;

/**
 * @Flow\Scope("singleton")
 */
final class FlashMessageProvider implements ProtectedContextAwareInterface
{
    private FlashMessageService $flashMessageService;

    public function __construct(FlashMessageService $flashMessageService)
    {
        $this->flashMessageService = $flashMessageService;
    }

    /**
     * @return array<int,Message>
     */
    public function getMessagesAndFlush(AbstractFusionObject $component): array
    {
        return $this->flashMessageService->getFlashMessageContainerForRequest(
            $component->getRuntime()->getControllerContext()->getRequest()
        )->getMessagesAndFlush();
    }

    /**
     * @param string $methodName
     */
    public function allowsCallOfMethod($methodName): bool
    {
        return true;
    }
}
