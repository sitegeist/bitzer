<?php declare(strict_types=1);
namespace Sitegeist\Bitzer\Application\Controller;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Controller\RestController;
use Sitegeist\Bitzer\Domain\Agent\AgentRepository;
use Sitegeist\Bitzer\Domain\Task\Schedule;

/**
 * @Flow\Scope("singleton")
 */
final class BitzerApiController extends RestController
{
    /**
     * @var array<int,string>
     */
    protected $supportedMediaTypes = ['application/json'];

    /**
     * @var array<string,class-string>
     */
    protected $viewFormatToObjectNameMap = ['json' => 'Neos\Flow\Mvc\View\JsonView'];

    private AgentRepository $agentRepository;

    private Schedule $schedule;

    private \DateInterval $upcomingInterval;

    public function __construct(
        AgentRepository $agentRepository,
        Schedule $schedule,
        string $upcomingInterval
    ) {
        $this->agentRepository = $agentRepository;
        $this->schedule = $schedule;
        $this->upcomingInterval = new \DateInterval($upcomingInterval);
    }

    public function dueTasksAction(): void
    {
        $uriBuilder = clone $this->getControllerContext()->getUriBuilder();
        $agents = $this->agentRepository->findCurrent();

        $this->view->assign('value', [
            'numberOfTasksDue' => $this->schedule->countDue($agents),
            'numberOfTasksPastDue' => $this->schedule->countPastDue($agents),
            'numberOfUpcomingTasks' => $this->schedule->countUpcoming($this->upcomingInterval, $agents),
            'links' => [
                'module' => $uriBuilder->reset()->uriFor(
                    'index',
                    ['module' => 'management/task'],
                    'Backend\\Module',
                    'Neos.Neos'
                )
            ]
        ]);
    }
}
