<?php
declare(strict_types=1);

namespace Sitegeist\Bitzer\Application\Controller;

use Neos\Error\Messages\Message;
use Neos\Flow\Http\Uri;
use Neos\Flow\I18n\Translator;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\View\ViewInterface;
use Neos\Flow\Security\Context as SecurityContext;
use Neos\Neos\Controller\Backend\ModuleController;
use Sitegeist\Bitzer\Application\Bitzer;
use Sitegeist\Bitzer\Domain\Agent\AgentRepository;
use Sitegeist\Bitzer\Domain\Task\Command\CancelTask;
use Sitegeist\Bitzer\Domain\Task\Command\CompleteTask;
use Sitegeist\Bitzer\Domain\Task\Command\ReassignTask;
use Sitegeist\Bitzer\Domain\Task\Command\RescheduleTask;
use Sitegeist\Bitzer\Domain\Task\Command\ScheduleTask;
use Sitegeist\Bitzer\Domain\Task\Command\SetNewTaskObject;
use Sitegeist\Bitzer\Domain\Task\Command\SetNewTaskTarget;
use Sitegeist\Bitzer\Domain\Task\Command\SetTaskProperties;
use Sitegeist\Bitzer\Domain\Task\ConstraintCheckResult;
use Sitegeist\Bitzer\Domain\Task\NodeAddress;
use Sitegeist\Bitzer\Domain\Task\Schedule;
use Sitegeist\Bitzer\Domain\Task\ScheduledTime;
use Sitegeist\Bitzer\Domain\Task\TaskClassName;
use Sitegeist\Bitzer\Domain\Task\TaskClassNameRepository;
use Sitegeist\Bitzer\Domain\Task\TaskIdentifier;
use Sitegeist\Bitzer\Infrastructure\FusionView;
use Sitegeist\Bitzer\Presentation\ComponentName;

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
     * @Flow\InjectConfiguration(path="upcomingInterval")
     * @var string
     */
    protected $upcomingInterval;

    /**
     * @Flow\Inject
     * @var TaskClassNameRepository
     */
    protected $taskClassNameRepository;

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
        $tasks = $this->schedule->findAllOrdered();
        $this->view->setFusionPath('schedule');
        $this->view->assignMultiple([
            'tasks' => $tasks,
            'taskClassNames' => $this->taskClassNameRepository->findAll(),
            'flashMessages' => $this->flashMessageContainer->getMessagesAndFlush()
        ]);
    }

    public function prepareTaskAction(TaskClassName $taskClassName): void
    {
        $this->view->setFusionPath('prepareTask');
        $this->view->assignMultiple([
            'taskClassName' => $taskClassName,
            'agents' => $this->agentRepository->findAll(),
            'componentName' => (string)ComponentName::fromTaskClassName($taskClassName, 'Prepare'),
            'flashMessages' => $this->flashMessageContainer->getMessagesAndFlush()
        ]);
    }

    public function scheduleTaskAction(TaskClassName $taskClassName, array $scheduledTime = [], string $agent = '', NodeAddress $object = null, Uri $target = null, array $properties = []): void
    {
        $constraintCheckResult = new ConstraintCheckResult();
        try {
            $scheduledTime = ScheduledTime::createFromArray($scheduledTime);
        } catch (\InvalidArgumentException $exception) {
            $scheduledTime = null;
        }
        $target = empty($target->getPath()) && empty($target->getHost()) ? null : $target;
        $command = new ScheduleTask(
            TaskIdentifier::create(),
            $taskClassName,
            $scheduledTime,
            $agent,
            $object,
            $target,
            $properties
        );

        $this->bitzer->handleScheduleTask($command, $constraintCheckResult);

        if ($constraintCheckResult->hasFailed()) {
            $this->response->setStatusCode(400);
            $this->addFlashMessage($this->getLabel('scheduleTask.failure', [$properties['description'] ?? '']), '', Message::SEVERITY_WARNING);
            $this->view->assignMultiple([
                'command' => $command,
                'constraintCheckResult' => $constraintCheckResult,
            ]);
            $this->prepareTaskAction($taskClassName);
        } else {
            $this->addFlashMessage($this->getLabel('scheduleTask.success', [$properties['description'], $scheduledTime->format('c')]), '');
            $this->redirect('schedule');
        }
    }

    public function myScheduleAction(): void
    {
        $agents = $this->agentRepository->findCurrent();
        $groupedTasks = $this->schedule->findPastDueDueAndUpcoming(new \DateInterval($this->upcomingInterval), $agents);

        $this->view->setFusionPath('mySchedule');
        $this->view->assignMultiple([
            'groupedTasks' => $groupedTasks,
            'flashMessages' => $this->flashMessageContainer->getMessagesAndFlush()
        ]);
    }

    public function editTaskAction(TaskIdentifier $taskIdentifier): void
    {
        $task = $this->schedule->findByIdentifier($taskIdentifier);
        if (!$task) {
            $this->addFlashMessage($this->getLabel('editTask.taskWasNotFound', [$task->getDescription()]), '', Message::SEVERITY_WARNING);
            $this->redirect('index');
        }
        $this->view->setFusionPath('editTask');
        $this->view->assignMultiple([
            'task' => $task,
            'componentName' => (string)ComponentName::fromTaskClassName(TaskClassName::createFromObject($task), 'Edit'),
            'agents' => $this->agentRepository->findAll(),
            'flashMessages' => $this->flashMessageContainer->getMessagesAndFlush()
        ]);
    }

    public function rescheduleTaskAction(TaskIdentifier $taskIdentifier, array $scheduledTime): void
    {
        $task = $this->schedule->findByIdentifier($taskIdentifier);
        $constraintCheckResult = new ConstraintCheckResult();
        try {
            $scheduledTime = ScheduledTime::createFromArray($scheduledTime);
        } catch (\InvalidArgumentException $exception) {
            $scheduledTime = null;
        }

        $command = new RescheduleTask($taskIdentifier, $scheduledTime);
        $this->bitzer->handleRescheduleTask($command, $constraintCheckResult);

        if ($constraintCheckResult->hasFailed()) {
            $this->response->setStatusCode(400);
            $this->addFlashMessage($this->getLabel('rescheduleTask.failure', [$task->getDescription()]), '', Message::SEVERITY_WARNING);
            $this->view->assignMultiple([
                'constraintCheckResult' => $constraintCheckResult,
            ]);
            $this->editTaskAction($taskIdentifier);
        } else {
            $this->addFlashMessage($this->getLabel('rescheduleTask.success', [$task->getDescription(), $scheduledTime->format('c')]), '');
            $this->redirect('editTask', null, null, ['taskIdentifier' => (string)$taskIdentifier]);
        }
    }

    public function reassignTaskAction(TaskIdentifier $taskIdentifier, string $agent): void
    {
        $task = $this->schedule->findByIdentifier($taskIdentifier);

        $constraintCheckResult = new ConstraintCheckResult();
        $command = new ReassignTask($taskIdentifier, $agent);

        $this->bitzer->handleReassignTask($command, $constraintCheckResult);

        if ($constraintCheckResult->hasFailed()) {
            $this->response->setStatusCode(400);
            $this->addFlashMessage($this->getLabel('reassignTask.failure', [$task->getDescription()]), '', Message::SEVERITY_WARNING);
            $this->view->assignMultiple([
                'constraintCheckResult' => $constraintCheckResult,
            ]);
            $this->editTaskAction($taskIdentifier);
        } else {
            $this->addFlashMessage($this->getLabel('reassignTask.success', [$task->getDescription(), $agent]), '');
            $this->redirect('editTask', null, null, ['taskIdentifier' => (string)$taskIdentifier]);
        }
    }

    public function setNewTaskTargetAction(TaskIdentifier $taskIdentifier, Uri $target): void
    {
        $task = $this->schedule->findByIdentifier($taskIdentifier);
        $target = empty($target->getPath()) && empty($target->getHost()) ? null : $target;

        $constraintCheckResult = new ConstraintCheckResult();
        $command = new SetNewTaskTarget($taskIdentifier, $target);

        $this->bitzer->handleSetNewTaskTarget($command, $constraintCheckResult);

        if ($constraintCheckResult->hasFailed()) {
            $this->response->setStatusCode(400);
            $this->addFlashMessage($this->getLabel('setNewTaskTarget.failure', [$task->getDescription()]), '', Message::SEVERITY_WARNING);
            $this->view->assignMultiple([
                'constraintCheckResult' => $constraintCheckResult,
            ]);
            $this->editTaskAction($taskIdentifier);
        } else {
            $this->addFlashMessage($this->getLabel('setNewTaskTarget.success', [$task->getDescription(), $target]), '');
            $this->redirect('editTask', null, null, ['taskIdentifier' => (string)$taskIdentifier]);
        }
    }

    public function setNewTaskObjectAction(TaskIdentifier $taskIdentifier, ?NodeAddress $object): void
    {
        $task = $this->schedule->findByIdentifier($taskIdentifier);

        $constraintCheckResult = new ConstraintCheckResult();
        $command = new SetNewTaskObject($taskIdentifier, $object);

        $this->bitzer->handleSetNewTaskObject($command, $constraintCheckResult);

        if ($constraintCheckResult->hasFailed()) {
            $this->response->setStatusCode(400);
            $this->addFlashMessage($this->getLabel('setNewTaskObject.failure', [$task->getDescription()]), '', Message::SEVERITY_WARNING);
            $this->view->assignMultiple([
                'constraintCheckResult' => $constraintCheckResult,
            ]);
            $this->editTaskAction($taskIdentifier);
        } else {
            $this->addFlashMessage($this->getLabel('setNewTaskObject.success', [$task->getDescription()]), '');
            $this->redirect('editTask', null, null, ['taskIdentifier' => (string)$taskIdentifier]);
        }
    }

    public function setTaskPropertiesAction(TaskIdentifier $taskIdentifier, array $properties): void
    {
        $task = $this->schedule->findByIdentifier($taskIdentifier);

        $constraintCheckResult = new ConstraintCheckResult();
        $command = new SetTaskProperties($taskIdentifier, $properties);

        $this->bitzer->handleSetTaskProperties($command);

        if ($constraintCheckResult->hasFailed()) {
            $this->response->setStatusCode(400);
            $this->addFlashMessage($this->getLabel('setTaskProperties.failure', [$task->getDescription()]), '', Message::SEVERITY_WARNING);
            $this->view->assignMultiple([
                'constraintCheckResult' => $constraintCheckResult,
            ]);
            $this->editTaskAction($taskIdentifier);
        } else {
            $this->addFlashMessage($this->getLabel('setTaskProperties.success', [$task->getDescription()]), '');
            $this->redirect('editTask', null, null, ['taskIdentifier' => (string)$taskIdentifier]);
        }
    }

    public function completeTaskAction(TaskIdentifier $taskIdentifier): void
    {
        $task = $this->schedule->findByIdentifier($taskIdentifier);

        $command = new CompleteTask($taskIdentifier);
        $this->bitzer->handleCompleteTask($command);

        $this->addFlashMessage($this->getLabel('completeTask.success', [$task->getDescription()]), '');
        $this->redirect('index');
    }

    public function cancelTaskAction(TaskIdentifier $taskIdentifier): void
    {
        $task = $this->schedule->findByIdentifier($taskIdentifier);

        $command = new CancelTask($taskIdentifier);
        $this->bitzer->handleCancelTask($command);

        $this->addFlashMessage($this->getLabel('cancelTask.success', [$task->getDescription()]), '');
        $this->redirect('index');
    }

    private function getLabel(string $labelIdentifier, array $arguments = [], $quantity = null): ?string
    {
        return $this->translator->translateById($labelIdentifier, $arguments, $quantity, null, 'Module.Bitzer', 'Sitegeist.Bitzer');
    }
}
