# Sitegeist.Bitzer

> A content lifecycle task manager for Neos CMS

![Screenshot of Task Reminder](./Documentation/screenshot.png)

## The content life cycle

From their initial creation until their final deletion, content repository nodes
pass different stages of maturity. A news article might be first published as an abstract
and then later on enriched with arbitrary media, then translated into a different language,
updated with references to other articles and so on. If these changes are to be implemented
in a planned way, we may model them as tasks.
Bitzer provides a task model, an overview for editors for tasks assigned to them
and an automation mechanism to generate new tasks, e.g. by defining a review date for a published news article.


## Installation

```
composer require sitegeist/bitzer
```

## Documentation

1. [User Interface](./Documentation/01_UserInterface.md)
2. [Building Blocks: Objects, Agents and Tasks](./Documentation/02_BuildingBlocks.md)
2. [Writing Custom Tasks](./Documentation/03_CustomTasks.md)
