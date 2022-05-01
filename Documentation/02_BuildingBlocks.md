<div align="center">
<a href="./01_UserInterface.md">< 1. User Interface</a>
    &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
    <a href="./00_Index.md">Index</a>
    &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
    <a href="./03_CustomTasks.md">2. Writing custom tasks &gt;</a>
</div>

---

# 2. Building Blocks

The Bitzer building blocks are modeled using schema.org. The main entities are
* Object (refers to a content repository node by its address)
* Agent (can be a user or user group)
* Task (https://schema.org/ScheduleAction)

## Object

Objects refer to nodes in the content repository, or rather their addresses to be precise.
The node address concept is borrowed from the new event-sourced content repository
and enables Bitzer to address nodes that do not yet exist.
For example, a translation task can refer to creating a node variant that is still to be created via translation.
> Hint: For custom development purposes, there is an object repository available.

## Agent

Agents can be all of Neos' backend users having Bitzer's agent role, as well as all roles that extend it.
Thus, tasks can be assigned to a single user or a user group.
> Hint: A data source for agents is available and used in the Setting.Agent mixin node type.
> For custom development purposes, there is also an agent repository available.

## Task

Each task can be described by its properties:
* it is scheduled for a certain **scheduledTime**
* it has a certain **status**, e.g. active or completed (see https://schema.org/ActionStatusType)
* it is to performed by a certain **agent**
* it is about a certain **object**, in our case a node address
* it has a **target** URI, which directs the agent to their user interface to perform the task

Additionally, tasks have metadata:
* a font awesome icon as an **image**
* a **description**
* arbitrary **properties** that might be relevant to the concrete task implementations

> Hint: For custom development purposes, there is a task repository named "Schedule" available.

Tasks themselves also have a lifecycle. They can be
* initially scheduled
* rescheduled to a different date
* reassigned to a different agent
* activated by an agent
* completed by an agent
* cancelled

These actions are modelled as commands that are handed over to Bitzer, the central application service.

---

<div align="center">
<a href="./01_UserInterface.md">< 1. User Interface</a>
    &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
    <a href="./00_Index.md">Index</a>
    &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
    <a href="./03_CustomTasks.md">2. Writing custom tasks &gt;</a>
</div>
