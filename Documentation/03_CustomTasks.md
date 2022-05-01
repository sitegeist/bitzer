<div align="center">
    <a href="./02_BuildingBlocks.md">< 2. Building Blocks &gt;</a>
        |
    <a href="./00_Index.md">Index</a>
</div>

---

# 3. Custom Tasks

While some generic tasks like review or translation will be provided by Bitzer's own plugin packages,
it is designed to allow for arbitrary tasks to be handled. There are a few steps to be taken for creating custom tasks.

> Hint: To see custom tasks in action, please refer to https://github.com/sitegeist/bitzer-review

## The task interface

Each task must implement the provided `Sitegeist\Bitzer\Domain\Task\TaskInterface`.
While most properties are just members passed on by the schedule, some are useful for customization.
* getShortType should return a unique short name for the task
* getImage should return a font awesome icon name that can be used by the Neos UI
* getDescription can return a custom description, e.g. using the task's the properties array
* getTarget should return the custom URI of the location the agent can perform the task

## The task factories

Each type of task can define its own factory. Those must implement `Sitegeist\Bitzer\Domain\Task\TaskFactoryInterface` and must be registered via config:
```yaml
Sitegeist:
  Bitzer:
    factories:
      'Acme\Package\Domain\Task\MyTask\MyTask': 'Acme\Package\Domain\Task\MyTask\MyTaskFactory'
```
If no custom factory is required, Bitzer's `GenericTaskFactory` is used.

## Handling tasks

The life cycle of custom tasks is to be implemented by the respective package.
Bitzer provides the same-named application service, repositories for tasks, objects and agents
and the signal `taskActionStatusUpdated` emitted by the `Schedule`.
What system events trigger automatic task generation is up to the specific use case.
For manually managing tasks there is a backend module available as well as a command controller.

## Testing

Bitzer provides feature traits for Flow's behavioral test framework based on Behat.
They can be used to
* create and simulate testing agents
* create testing objects
* perform commands on tasks

to enable custom implementations with ample testing capabilities

---

<div align="center">
    <a href="./02_BuildingBlocks.md">< 2. Building Blocks &gt;</a>
        |
    <a href="./00_Index.md">Index</a>
</div>
