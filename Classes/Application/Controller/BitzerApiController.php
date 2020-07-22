<?php declare(strict_types=1);
namespace Sitegeist\Bitzer\Application\Controller;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Controller\RestController;

use Sitegeist\Bitzer\Domain\Agent\AgentRepository;
use Sitegeist\Bitzer\Domain\Task\Schedule;

/**
 * @Flow\Scope("singleton")
 */
class BitzerApiController extends RestController
{
    /**
     * @Flow\Inject
     * @var AgentRepository
     */
    protected $agentRepository;

    /**
     * @Flow\Inject
     * @var Schedule
     */
    protected $schedule;

    /**
     * @var array
     */
    protected $supportedMediaTypes = ['application/json'];

    /**
     * @var array
     */
    protected $viewFormatToObjectNameMap = ['json' => 'Neos\Flow\Mvc\View\JsonView'];

    /**
     * @return void
     */
    public function dueTasksAction(): void
    {
        $agents = $this->agentRepository->findCurrent();

        $this->view->assign('value', [
            'numberOfTasksDue' => $this->schedule->countDue($agents),
            'numberOfTasksPastDue' => $this->schedule->countPastDue($agents),
            'numberOfUpcomingTasks' => $this->schedule->countPastDue($agents)
        ]);
    }
}