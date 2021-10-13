<?php declare(strict_types=1);
namespace Sitegeist\Bitzer\Application\Controller;

use GuzzleHttp\Psr7\Uri;
use Neos\Error\Messages\Message;
use Neos\Flow\I18n\Translator;
use Neos\Flow\Annotations as Flow;
use Neos\Neos\Controller\Backend\ModuleController;
use Sitegeist\Bitzer\Application\Bitzer;
use Sitegeist\Bitzer\Domain\Agent\AgentIdentifier;
use Sitegeist\Bitzer\Domain\Agent\AgentRepository;
use Sitegeist\Bitzer\Domain\Task\Command\ActivateTask;
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
 *
 * @Flow\Scope("singleton")
 */
final class BitzerController extends ModuleController
{
    /**
     * @var string
     */
    protected $defaultViewObjectName = FusionView::class;

    /**
     * @var FusionView
     */
    protected $view;

    private Bitzer $bitzer;

    private Schedule $schedule;

    private AgentRepository $agentRepository;

    private Translator $translator;

    private TaskClassNameRepository $taskClassNameRepository;

    private \DateInterval $upcomingInterval;

    public function __construct(
        Bitzer $bitzer,
        Schedule $schedule,
        AgentRepository $agentRepository,
        Translator $translator,
        TaskClassNameRepository $taskClassNameRepository,
        string $upcomingInterval
    ) {
        $this->bitzer = $bitzer;
        $this->schedule = $schedule;
        $this->agentRepository = $agentRepository;
        $this->translator = $translator;
        $this->taskClassNameRepository = $taskClassNameRepository;
        $this->upcomingInterval = new \DateInterval($upcomingInterval);
    }

    public function indexAction(array $module = []): void
    {
        if (!$this->securityContext->hasRole('Sitegeist.Bitzer:Administrator')) {
            $this->redirect('mySchedule');
        }

        $this->view->setFusionPath('index');
        $this->view->assignMultiple([
            'taskClassNames' => $this->getTaskClassNameOptions()
        ]);
    }

    public function scheduleAction(): void
    {
        $tasks = $this->schedule->findAllOrdered();
        $this->view->setFusionPath('schedule');
        $this->view->assignMultiple([
            'tasks' => $tasks,
            'taskClassNames' => $this->getTaskClassNameOptions(),
            'labels' => [
                'task.scheduledTime.label' => $this->getLabel('task.scheduledTime.label'),
                'task.actionStatus.label' => $this->getLabel('task.actionStatus.label'),
                'task.type.label' => $this->getLabel('task.type.label'),
                'task.properties.description.label' => $this->getLabel('task.properties.description.label'),
                'task.agent.label' => $this->getLabel('task.agent.label'),
                'task.object.label' => $this->getLabel('task.object.label'),
                'actions.label' => $this->getLabel('actions.label'),
                'actionStatusType.https://schema.org/ActiveActionStatus.label' => $this->getLabel('actionStatusType.https://schema.org/ActiveActionStatus.label'),
                'actionStatusType.https://schema.org/CompletedActionStatus.label' => $this->getLabel('actionStatusType.https://schema.org/CompletedActionStatus.label'),
                'actionStatusType.https://schema.org/FailedActionStatus.label' => $this->getLabel('actionStatusType.https://schema.org/FailedActionStatus.label'),
                'actionStatusType.https://schema.org/PotentialActionStatus.label' => $this->getLabel('actionStatusType.https://schema.org/PotentialActionStatus.label'),
                'actions.prepareTask.label' => $this->getLabel('actions.prepareTask.label')
            ]
        ]);
    }

    public function prepareTaskAction(string $taskClassName): void
    {
        $this->view->setFusionPath('prepareTask');
        $this->view->assignMultiple([
            'taskClassName' => $taskClassName,
            'agents' => $this->agentRepository->findAll(),
            'componentName' => (string)ComponentName::fromTaskClassName(TaskClassName::createFromString($taskClassName), 'Prepare')
        ]);
    }

    public function scheduleTaskAction(string $taskClassName, string $agent, array $scheduledTime = [], string $object = null, Uri $target = null, array $properties = []): void
    {
        $constraintCheckResult = new ConstraintCheckResult();
        try {
            $scheduledTime = ScheduledTime::createFromArray($scheduledTime);
        } catch (\InvalidArgumentException $exception) {
            $scheduledTime = null;
        }
        if ($target instanceof Uri && empty($target->getPath()) && empty($target->getHost())) {
            $target = null;
        }
        $command = new ScheduleTask(
            TaskIdentifier::create(),
            TaskClassName::createFromString($taskClassName),
            $scheduledTime,
            $this->agentRepository->findByIdentifier(AgentIdentifier::fromString($agent)),
            $object ? NodeAddress::fromJsonString($object) : null,
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
        $groupedTasks = $this->schedule->findPastDueDueAndUpcoming($this->upcomingInterval, $agents);

        $this->view->setFusionPath('mySchedule');
        $this->view->assignMultiple([
            'groupedTasks' => $groupedTasks,
            'labels' => [
                'task.scheduledTime.label' => $this->getLabel('task.scheduledTime.label'),
                'task.actionStatus.label' => $this->getLabel('task.actionStatus.label'),
                'task.type.label' => $this->getLabel('task.type.label'),
                'task.properties.description.label' => $this->getLabel('task.properties.description.label'),
                'task.agent.label' => $this->getLabel('task.agent.label'),
                'task.object.label' => $this->getLabel('task.object.label'),
                'actions.label' => $this->getLabel('actions.label'),
                'taskDueStatusType.due.label' => $this->getLabel('taskDueStatusType.due.label'),
                'taskDueStatusType.upcoming.due' => $this->getLabel('taskDueStatusType.upcoming.label'),
                'taskDueStatusType.pastDue.label' => $this->getLabel('taskDueStatusType.pastDue.label'),
                'actionStatusType.https://schema.org/ActiveActionStatus.label' => $this->getLabel('actionStatusType.https://schema.org/ActiveActionStatus.label'),
                'actionStatusType.https://schema.org/CompletedActionStatus.label' => $this->getLabel('actionStatusType.https://schema.org/CompletedActionStatus.label'),
                'actionStatusType.https://schema.org/FailedActionStatus.label' => $this->getLabel('actionStatusType.https://schema.org/FailedActionStatus.label'),
                'actionStatusType.https://schema.org/PotentialActionStatus.label' => $this->getLabel('actionStatusType.https://schema.org/PotentialActionStatus.label')
            ]
        ]);
    }

    public function editTaskAction(string $taskIdentifier): void
    {
        $task = $this->schedule->findByIdentifier(new TaskIdentifier($taskIdentifier));
        if (!$task) {
            $this->addFlashMessage($this->getLabel('editTask.taskWasNotFound', [$task->getDescription()]), '', Message::SEVERITY_WARNING);
            $this->redirect('schedule');
        }
        $this->view->setFusionPath('editTask');
        $this->view->assignMultiple([
            'task' => $task,
            'componentName' => (string)ComponentName::fromTaskClassName(TaskClassName::createFromObject($task), 'Edit'),
            'agents' => $this->agentRepository->findAll()
        ]);
    }

    public function rescheduleTaskAction(string $taskIdentifier, array $scheduledTime): void
    {
        $taskIdentifierObject = new TaskIdentifier($taskIdentifier);
        $task = $this->schedule->findByIdentifier($taskIdentifierObject);
        $constraintCheckResult = new ConstraintCheckResult();
        try {
            $scheduledTime = ScheduledTime::createFromArray($scheduledTime);
        } catch (\InvalidArgumentException $exception) {
            $scheduledTime = null;
        }

        $command = new RescheduleTask($taskIdentifierObject, $scheduledTime);
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

    public function reassignTaskAction(string $taskIdentifier, string $agent): void
    {
        $taskIdentifierObject = new TaskIdentifier($taskIdentifier);
        $task = $this->schedule->findByIdentifier($taskIdentifierObject);

        $constraintCheckResult = new ConstraintCheckResult();
        $command = new ReassignTask(
            $taskIdentifierObject,
            $this->agentRepository->findByIdentifier(AgentIdentifier::fromString($agent))
        );

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

    public function setNewTaskTargetAction(string $taskIdentifier, Uri $target): void
    {
        $taskIdentifierObject = new TaskIdentifier($taskIdentifier);
        $task = $this->schedule->findByIdentifier($taskIdentifierObject);
        $target = empty($target->getPath()) && empty($target->getHost()) ? null : $target;

        $constraintCheckResult = new ConstraintCheckResult();
        $command = new SetNewTaskTarget($taskIdentifierObject, $target);

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

    public function setNewTaskObjectAction(string $taskIdentifier, array $object): void
    {
        $taskIdentifierObject = new TaskIdentifier($taskIdentifier);
        $task = $this->schedule->findByIdentifier($taskIdentifierObject);

        $constraintCheckResult = new ConstraintCheckResult();
        $command = new SetNewTaskObject($taskIdentifierObject, NodeAddress::fromArray($object));

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
            $this->redirect('editTask', null, null, ['taskIdentifier' => $taskIdentifier]);
        }
    }

    public function setTaskPropertiesAction(string $taskIdentifier, array $properties): void
    {
        $taskIdentifierObject = new TaskIdentifier($taskIdentifier);
        $task = $this->schedule->findByIdentifier($taskIdentifierObject);

        $constraintCheckResult = new ConstraintCheckResult();
        $command = new SetTaskProperties($taskIdentifierObject, $properties);

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
            $this->redirect('editTask', null, null, ['taskIdentifier' => $taskIdentifier]);
        }
    }

    public function activateTaskAction(string $taskIdentifier): void
    {
        $taskIdentifierObject = new TaskIdentifier($taskIdentifier);
        $task = $this->schedule->findByIdentifier($taskIdentifierObject);

        $command = new ActivateTask($taskIdentifierObject);
        $this->bitzer->handleActivateTask($command);

        if ($task->getTarget()) {
            $this->redirectToUri($task->getTarget());
        } else {
            $this->addFlashMessage($this->getLabel('activateTask.success', [$task->getDescription()]), '');
            $this->redirect('mySchedule');
        }
    }

    public function completeTaskAction(string $taskIdentifier): void
    {
        $taskIdentifierObject = new TaskIdentifier($taskIdentifier);
        $task = $this->schedule->findByIdentifier($taskIdentifierObject);

        $command = new CompleteTask($taskIdentifierObject);
        $this->bitzer->handleCompleteTask($command);

        $this->addFlashMessage($this->getLabel('completeTask.success', [$task->getDescription()]), '');
        $this->redirect('mySchedule');
    }

    public function cancelTaskAction(string $taskIdentifier): void
    {
        $taskIdentifierObject = new TaskIdentifier($taskIdentifier);
        $task = $this->schedule->findByIdentifier($taskIdentifierObject);

        $command = new CancelTask($taskIdentifierObject);
        $this->bitzer->handleCancelTask($command);

        $this->addFlashMessage($this->getLabel('cancelTask.success', [$task->getDescription()]), '');
        $this->redirect('schedule');
    }

    /**
     * @return array<int,array<string,string>>
     */
    private function getTaskClassNameOptions(): array
    {
        return array_map(function (TaskClassName $taskClassName): array {
            $id = 'taskClassName.' . $taskClassName->getValue() . '.label';
            return [
                'identifier' => $taskClassName->getValue(),
                'label' => $this->getLabel($id)
            ];
        }, $this->taskClassNameRepository->findAll()->getIterator()->getArrayCopy());
    }

    private function getLabel(string $labelIdentifier, array $arguments = [], $quantity = null): string
    {
        return $this->translator->translateById(
            $labelIdentifier,
            $arguments,
            $quantity,
            null,
            'Module.Bitzer',
            'Sitegeist.Bitzer'
        ) ?: $labelIdentifier;
    }
}
