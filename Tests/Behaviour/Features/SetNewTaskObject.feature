@fixtures
Feature: Set new task object

  As a user of Bitzer I want to be able to set a new object for a task

  Background:
    Given I have the following content dimensions:
      | Identifier | Default |
    And I have the following NodeTypes configuration:
    """
    'unstructured': []
    'Sitegeist.Bitzer:Testing.Document':
      properties:
        title:
          type: string
    """
    And I have the following nodes:
      | Identifier             | Path                          | Node Type                         | Properties                          | Workspace |
      | sites                  | /sites                        | unstructured                      | {}                                  | live      |
      | nody-mc-nodeface       | /sites/sity-mc-siteface       | Sitegeist.Bitzer:Testing.Document | {"title": "Nody McNodeface"}        | live      |
      | sir-david-nodenborough | /sites/sir-david-nodenborough | Sitegeist.Bitzer:Testing.Document | {"title": "Sir Nodeward Nodington"} | live      |
    And I have the following additional agents:
    """
    'Sitegeist.Bitzer:TestingAgent':
      parentRoles: ['Sitegeist.Bitzer:Agent']
    'Sitegeist.Bitzer:TestingAdministrator':
      parentRoles: ['Sitegeist.Bitzer:Administrator']
    """
    And the command ScheduleTask is executed with payload:
      | Key            | Value                                                                                            |
      | taskIdentifier | "tasky-mc-taskface"                                                                              |
      | taskClassName  | "Sitegeist\\Bitzer\\Domain\\Task\\Generic\\GenericTask"                                          |
      | scheduledTime  | "2020-01-02T00:00:00+00:00"                                                                      |
      | agent          | "Sitegeist.Bitzer:TestingAgent"                                                                  |
      | properties     | {"description":"task description"}                                                               |
      | object         | {"nodeAggregateIdentifier":"nody-mc-nodeface", "workspaceName":"live", "dimensionSpacePoint":{}} |

  Scenario: Try to set a new object for a non-existing task
    When the command SetNewTaskObject is executed with payload and exceptions are caught:
      | Key            | Value                                                                                                  |
      | taskIdentifier | "i-do-not-exist"                                                                                       |
      | object         | {"nodeAggregateIdentifier":"sir-david-nodenborough", "workspaceName":"live", "dimensionSpacePoint":{}} |
    Then the last command should have thrown an exception of type "TaskDoesNotExist"

  Scenario: Try to set a new object for a non-existing task using a constraint check result
    Given exceptions are collected in a constraint check result
    When the command SetNewTaskObject is executed with payload:
      | Key            | Value                                                                                                  |
      | taskIdentifier | "i-do-not-exist"                                                                                       |
      | object         | {"nodeAggregateIdentifier":"sir-david-nodenborough", "workspaceName":"live", "dimensionSpacePoint":{}} |
    Then I expect the constraint check result to contain an exception of type "TaskDoesNotExist" at path "identifier"

  Scenario: Try to set a new invalid object
    When the command SetNewTaskObject is executed with payload and exceptions are caught:
      | Key            | Value                                                                                          |
      | taskIdentifier | "tasky-mc-taskface"                                                                            |
      | object         | {"nodeAggregateIdentifier":"i-do-not-exist", "workspaceName":"live", "dimensionSpacePoint":{}} |
    Then the last command should have thrown an exception of type "ObjectDoesNotExist"

  Scenario: Try to set a new invalid object using a constraint check result
    Given exceptions are collected in a constraint check result
    When the command SetNewTaskObject is executed with payload:
      | Key            | Value                                                                                          |
      | taskIdentifier | "tasky-mc-taskface"                                                                            |
      | object         | {"nodeAggregateIdentifier":"i-do-not-exist", "workspaceName":"live", "dimensionSpacePoint":{}} |
    Then I expect the constraint check result to contain an exception of type "ObjectDoesNotExist" at path "object"
    And I expect the task "tasky-mc-taskface" to exist
    And I expect this task to be about '{"nodeAggregateIdentifier":"nody-mc-nodeface", "workspaceName":"live", "dimensionSpacePoint":{}}'

  Scenario: Remove the target from a GenericTask
    When the command SetNewTaskObject is executed with payload:
      | Key            | Value               |
      | taskIdentifier | "tasky-mc-taskface" |
      | object         | null                |
    Then I expect the task "tasky-mc-taskface" to exist
    And I expect this task to be about nothing

  Scenario: Set a new valid object to an existing task
    When the command SetNewTaskObject is executed with payload:
      | Key            | Value                                                                                                  |
      | taskIdentifier | "tasky-mc-taskface"                                                                                    |
      | object         | {"nodeAggregateIdentifier":"sir-david-nodenborough", "workspaceName":"live", "dimensionSpacePoint":{}} |
    Then I expect the task "tasky-mc-taskface" to exist
    And I expect this task to be about '{"nodeAggregateIdentifier":"sir-david-nodenborough", "workspaceName":"live", "dimensionSpacePoint":{}}'
