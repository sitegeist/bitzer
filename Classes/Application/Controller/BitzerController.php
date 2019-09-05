<?php
declare(strict_types=1);

namespace Sitegeist\Bitzer\Application\Controller;

use Neos\Flow\I18n\Translator;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\View\ViewInterface;
use Neos\Flow\Security\Context as SecurityContext;
use Neos\Fusion\View\FusionView;
use Neos\Neos\Controller\Backend\ModuleController;
use Sitegeist\Bitzer\Application\Bitzer;
use Sitegeist\Bitzer\Domain\Agent\AgentRepository;
use Sitegeist\Bitzer\Domain\Task\Command\CompleteTask;
use Sitegeist\Bitzer\Domain\Task\Schedule;
use Sitegeist\Bitzer\Domain\Task\TaskIdentifier;

/**
 * The bitzer controller for schedule actions
 */
class BitzerController extends ModuleController
{
    /**
     * @Flow\Inject
     * @var Bitzer
     */
    protected $bitzer;

    /**
     * @Flow\Inject
     * @var Schedule
     */
    protected $schedule;

    /**
     * @Flow\Inject
     * @var SecurityContext
     */
    protected $securityContext;

    /**
     * @Flow\Inject
     * @var AgentRepository
     */
    protected $agentRepository;

    /**
     * @Flow\Inject
     * @var Translator
     */
    protected $translator;

    /**
     * @var string
     */
    protected $defaultViewObjectName = FusionView::class;

    /**
     * @var FusionView
     */
    protected $view;

    protected function initializeView(ViewInterface $view)
    {
        parent::initializeView($view);
    }

    public function indexAction(array $module = []): void
    {
        if ($this->securityContext->hasRole('Sitegeist.Bitzer:Administrator')) {
            $this->forward('schedule');
        } else {
            $this->forward('mySchedule');
        }
    }

    public function scheduleAction(): void
    {

    }

    public function myScheduleAction(): void
    {
        $agents = $this->agentRepository->findCurrent();
        $tasks = $this->schedule->findForAgents($agents);

        $this->view->setFusionPath('mySchedule');
        $this->view->assignMultiple([
            'tasks' => $tasks,
            'flashMessages' => $this->flashMessageContainer->getMessagesAndFlush()
        ]);
    }

    public function showTaskAction(TaskIdentifier $taskIdentifier): void
    {

    }

    public function completeTaskAction(TaskIdentifier $taskIdentifier): void
    {
        $task = $this->schedule->findByIdentifier($taskIdentifier);

        $command = new CompleteTask($taskIdentifier);
        $this->bitzer->handleCompleteTask($command);

        $this->addFlashMessage($this->getLabel('completeTask.success', [$task->getDescription()]), '');
        $this->redirect('index');
    }

    private function getLabel(string $labelIdentifier, array $arguments = [], $quantity = null): ?string
    {
        return $this->translator->translateById($labelIdentifier, $arguments, $quantity, null, 'Module.Bitzer', 'Sitegeist.Bitzer');
    }
}
