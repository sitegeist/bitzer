<?php
declare(strict_types=1);

namespace Sitegeist\Bitzer\Application\Controller;

use Neos\Flow\Mvc\Controller\ActionController;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\View\ViewInterface;
use Neos\Flow\Security\Context as SecurityContext;
use Neos\Fusion\View\FusionView;
use Sitegeist\Bitzer\Application\Bitzer;
use Sitegeist\Bitzer\Domain\Agent\AgentRepository;
use Sitegeist\Bitzer\Domain\Task\Schedule;
use Sitegeist\Bitzer\Domain\Task\TaskIdentifier;

/**
 * The bitzer controller for schedule actions
 */
class BitzerController extends ActionController
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

    public function indexAction(): void
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
        $this->view->assign('tasks', $tasks);
    }

    public function showTaskAction(TaskIdentifier $taskIdentifier): void
    {

    }
}
