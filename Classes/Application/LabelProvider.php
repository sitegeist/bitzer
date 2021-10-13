<?php declare(strict_types=1);
namespace Sitegeist\Bitzer\Application;

use Neos\Eel\ProtectedContextAwareInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\I18n\Translator;

/**
 * @Flow\Scope("singleton")
 */
final class LabelProvider implements ProtectedContextAwareInterface
{
    private Translator $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @return array<string,string>
     */
    public function forRescheduleTaskForm(): array
    {
        return [
            'rescheduleTask.label' => $this->getLabel('rescheduleTask.label'),
            'task.scheduledTime.date' => $this->getLabel('task.scheduledTime.date'),
            'task.scheduledTime.time' => $this->getLabel('task.scheduledTime.time')
        ];
    }

    /**
     * @return array<string,string>
     */
    public function forReassignTaskForm(): array
    {
        return [
            'reassignTask.label' => $this->getLabel('reassignTask.label'),
            'task.agent.label' => $this->getLabel('task.agent.label'),
            'task.scheduledTime.time' => $this->getLabel('task.scheduledTime.time')
        ];
    }

    /**
     * @return array<string,string>
     */
    public function forSetTaskPropertiesForm(): array
    {
        return [
            'setTaskProperties.label' => $this->getLabel('setTaskProperties.label'),
            'task.properties.description.label' => $this->getLabel('task.properties.description.label'),
            'task.properties.description.placeholder' => $this->getLabel('task.properties.description.placeholder')
        ];
    }

    /**
     * @return array<string,string>
     */
    public function forSetNewTaskTargetForm(): array
    {
        return [
            'setNewTaskTarget.label' => $this->getLabel('setNewTaskTarget.label'),
            'task.target.label' => $this->getLabel('task.target.label'),
            'task.target.placeholder' => $this->getLabel('task.target.placeholder')
        ];
    }

    /**
     * @return array<string,string>
     */
    public function forSetNewTaskObjectForm(): array
    {
        return [
            'setNewTaskObject.label' => $this->getLabel('setNewTaskObject.label') ?: 'wat'
        ];
    }

    /**
     * @return array<string,string>
     */
    public function forAgentComponent(): array
    {
        return [
            'task.agent.label' => $this->getLabel('task.agent.label')
        ];
    }

    /**
     * @return array<string,string>
     */
    public function forDescriptionComponent(): array
    {
        return [
            'task.properties.label' => $this->getLabel('task.properties.label'),
            'task.properties.description.label' => $this->getLabel('task.properties.description.label'),
            'task.properties.description.placeholder' => $this->getLabel('task.properties.description.placeholder')
        ];
    }

    /**
     * @return array<string,string>
     */
    public function forObjectComponent(): array
    {
        return [
            'task.object.label' => $this->getLabel('task.object.label'),
            'setNewTaskObject.label' => $this->getLabel('setNewTaskObject.label')
        ];
    }

    /**
     * @return array<string,string>
     */
    public function forScheduledDateComponent(): array
    {
        return [
            'task.scheduledTime.label' => $this->getLabel('task.scheduledTime.label'),
            'task.scheduledTime.date' => $this->getLabel('task.scheduledTime.date'),
            'task.scheduledTime.time' => $this->getLabel('task.scheduledTime.time')
        ];
    }

    /**
     * @return array<string,string>
     */
    public function forTargetComponent(): array
    {
        return [
            'task.target.label' => $this->getLabel('task.target.label'),
            'task.target.placeholder' => $this->getLabel('task.target.placeholder')
        ];
    }

    /**
     * @return array<string,string>
     */
    public function forPrepareGenericTaskForm(): array
    {
        return [
            'scheduleTask.label' => $this->getLabel('scheduleTask.label'),
            'actions.cancel.label' => $this->getLabel('actions.cancel.label'),
        ];
    }

    /**
     * @return array<string,string>
     */
    public function forEditGenericTaskForm(): array
    {
        return [
            'actions.back.label' => $this->getLabel('actions.back.label')
        ];
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
        );
    }

    /**
     * @param string $methodName
     */
    public function allowsCallOfMethod($methodName): bool
    {
        return true;
    }
}
