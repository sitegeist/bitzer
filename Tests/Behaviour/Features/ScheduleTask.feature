@fixtures
Feature: Schedule task

  As a user of Bitzer I want to be able to schedule a task

  Background:
    Given I have the following additional agents:
    """
    'Sitegeist.Bitzer:TestingAgent':
      parentRoles: ['Sitegeist.Bitzer:Agent']
    'Sitegeist.Bitzer:TestingAdministrator':
      parentRoles: ['Sitegeist.Bitzer:Administrator']
    """

  Scenario: Try to schedule a task using an already taken identifier
    Given the command ScheduleTask is executed with payload:
      | Key            | Value                                                   |
      | taskIdentifier | "tasky-mc-taskface"                                     |
      | taskClassName  | "Sitegeist\\Bitzer\\Domain\\Task\\Generic\\GenericTask" |
      | scheduledTime  | "2020-01-02T00:00:00+00:00"                             |
      | agent          | "Sitegeist.Bitzer:TestingAgent"                         |
      | properties     | {"description":"task description"}                      |
    When the command ScheduleTask is executed with payload and exceptions are caught:
      | Key            | Value                                                   |
      | taskIdentifier | "tasky-mc-taskface"                                     |
      | taskClassName  | "Sitegeist\\Bitzer\\Domain\\Task\\Generic\\GenericTask" |
      | scheduledTime  | "2020-01-02T00:00:00+00:00"                             |
      | agent          | "Sitegeist.Bitzer:TestingAgent"                         |
      | properties     | {"description":"task description"}                      |
    Then the last command should have thrown an exception of type "TaskDoesExist"

  Scenario: Try to schedule a task using an already taken identifier using a constraint check result
    Given exceptions are collected in a constraint check result
    And the command ScheduleTask is executed with payload:
      | Key            | Value                                                   |
      | taskIdentifier | "tasky-mc-taskface"                                     |
      | taskClassName  | "Sitegeist\\Bitzer\\Domain\\Task\\Generic\\GenericTask" |
      | scheduledTime  | "2020-01-02T00:00:00+00:00"                             |
      | agent          | "Sitegeist.Bitzer:TestingAgent"                         |
      | properties     | {"description":"task description"}                      |
    When the command ScheduleTask is executed with payload:
      | Key            | Value                                                   |
      | taskIdentifier | "tasky-mc-taskface"                                     |
      | taskClassName  | "Sitegeist\\Bitzer\\Domain\\Task\\Generic\\GenericTask" |
      | scheduledTime  | "2021-01-02T00:00:00+00:00"                             |
      | agent          | "Sitegeist.Bitzer:TestingAgent"                         |
      | properties     | {"description":"other task description"}                |
    Then I expect the constraint check result to contain an exception of type "TaskDoesExist" at path "identifier"
    And I expect the task "tasky-mc-taskface" to exist
    And I expect this task to have the properties:
      | Key         | Value            |
      | description | task description |

  Scenario: Try to schedule a task without a scheduled time
    When the command ScheduleTask is executed with payload and exceptions are caught:
      | Key            | Value                                                   |
      | taskIdentifier | "tasky-mc-taskface"                                     |
      | taskClassName  | "Sitegeist\\Bitzer\\Domain\\Task\\Generic\\GenericTask" |
      | agent          | "Sitegeist.Bitzer:TestingAgent"                         |
      | properties     | {"description":"task description"}                      |
    Then the last command should have thrown an exception of type "ScheduledTimeIsUndefined"

  Scenario: Try to schedule a task without a scheduled time using a constraint check result
    Given exceptions are collected in a constraint check result
    When the command ScheduleTask is executed with payload:
      | Key            | Value                                                   |
      | taskIdentifier | "tasky-mc-taskface"                                     |
      | taskClassName  | "Sitegeist\\Bitzer\\Domain\\Task\\Generic\\GenericTask" |
      | agent          | "Sitegeist.Bitzer:TestingAgent"                         |
      | properties     | {"description":"task description"}                      |
    Then I expect the constraint check result to contain an exception of type "ScheduledTimeIsUndefined" at path "scheduledTime"
    And I expect the task "tasky-mc-taskface" not to exist

  Scenario: Try to schedule a task without a valid agent
    When the command ScheduleTask is executed with payload and exceptions are caught:
      | Key            | Value                                                   |
      | taskIdentifier | "tasky-mc-taskface"                                     |
      | taskClassName  | "Sitegeist\\Bitzer\\Domain\\Task\\Generic\\GenericTask" |
      | scheduledTime  | "2020-01-02T00:00:00+00:00"                             |
      | agent          | "I.Do:NotExist"                                         |
      | properties     | {"description":"task description"}                      |
    Then the last command should have thrown an exception of type "AgentDoesNotExist"

  Scenario: Try to schedule a task without a valid agent using a constraint check result
    Given exceptions are collected in a constraint check result
    When the command ScheduleTask is executed with payload:
      | Key            | Value                                                   |
      | taskIdentifier | "tasky-mc-taskface"                                     |
      | taskClassName  | "Sitegeist\\Bitzer\\Domain\\Task\\Generic\\GenericTask" |
      | scheduledTime  | "2020-01-02T00:00:00+00:00"                             |
      | agent          | "I.Do:NotExist"                                         |
      | properties     | {"description":"task description"}                      |
    Then I expect the constraint check result to contain an exception of type "AgentDoesNotExist" at path "agent"
    And I expect the task "tasky-mc-taskface" not to exist

  Scenario: Try to schedule a task without a description
    When the command ScheduleTask is executed with payload and exceptions are caught:
      | Key            | Value                                                   |
      | taskIdentifier | "tasky-mc-taskface"                                     |
      | taskClassName  | "Sitegeist\\Bitzer\\Domain\\Task\\Generic\\GenericTask" |
      | scheduledTime  | "2020-01-02T00:00:00+00:00"                             |
      | agent          | "Sitegeist.Bitzer:TestingAgent"                         |
      | properties     | {}                                                      |
    Then the last command should have thrown an exception of type "DescriptionIsInvalid"

  Scenario: Try to schedule a task without a description using a constraint check result
    Given exceptions are collected in a constraint check result
    When the command ScheduleTask is executed with payload:
      | Key            | Value                                                   |
      | taskIdentifier | "tasky-mc-taskface"                                     |
      | taskClassName  | "Sitegeist\\Bitzer\\Domain\\Task\\Generic\\GenericTask" |
      | scheduledTime  | "2020-01-02T00:00:00+00:00"                             |
      | agent          | "Sitegeist.Bitzer:TestingAgent"                         |
      | properties     | {}                                                      |
    Then I expect the constraint check result to contain an exception of type "DescriptionIsInvalid" at path "properties.description"
    And I expect the task "tasky-mc-taskface" not to exist

  Scenario: Try to schedule a task with an invalid target
    When the command ScheduleTask is executed with payload and exceptions are caught:
      | Key            | Value                                                   |
      | taskIdentifier | "tasky-mc-taskface"                                     |
      | taskClassName  | "Sitegeist\\Bitzer\\Domain\\Task\\Generic\\GenericTask" |
      | scheduledTime  | "2020-01-02T00:00:00+00:00"                             |
      | agent          | "Sitegeist.Bitzer:TestingAgent"                         |
      | properties     | {"description":"task description"}                      |
      | target         | "this-is-not-a-uri"                                     |
    Then the last command should have thrown an exception of type "TargetIsInvalid"

  Scenario: Try to schedule a task with an invalid target using a constraint check result
    Given exceptions are collected in a constraint check result
    When the command ScheduleTask is executed with payload:
      | Key            | Value                                                   |
      | taskIdentifier | "tasky-mc-taskface"                                     |
      | taskClassName  | "Sitegeist\\Bitzer\\Domain\\Task\\Generic\\GenericTask" |
      | scheduledTime  | "2020-01-02T00:00:00+00:00"                             |
      | agent          | "Sitegeist.Bitzer:TestingAgent"                         |
      | properties     | {"description":"task description"}                      |
      | target         | "this-is-not-a-uri"                                     |
    Then I expect the constraint check result to contain an exception of type "TargetIsInvalid" at path "target"
    And I expect the task "tasky-mc-taskface" not to exist

  Scenario: Try to schedule a task with an invalid object
    When the command ScheduleTask is executed with payload and exceptions are caught:
      | Key            | Value                                                                                                |
      | taskIdentifier | "tasky-mc-taskface"                                                                                  |
      | taskClassName  | "Sitegeist\\Bitzer\\Domain\\Task\\Generic\\GenericTask"                                              |
      | scheduledTime  | "2020-01-02T00:00:00+00:00"                                                                          |
      | agent          | "Sitegeist.Bitzer:TestingAgent"                                                                      |
      | properties     | {"description":"task description"}                                                                   |
      | object         | {"nodeAggregateIdentifier":"i-do-not-exist", "workspaceName":"me-neither", "dimensionSpacePoint":{}} |
    Then the last command should have thrown an exception of type "ObjectDoesNotExist"

  Scenario: Try to schedule a task with an invalid object using a constraint check result
    Given exceptions are collected in a constraint check result
    When the command ScheduleTask is executed with payload:
      | Key            | Value                                                                                                |
      | taskIdentifier | "tasky-mc-taskface"                                                                                  |
      | taskClassName  | "Sitegeist\\Bitzer\\Domain\\Task\\Generic\\GenericTask"                                              |
      | scheduledTime  | "2020-01-02T00:00:00+00:00"                                                                          |
      | agent          | "Sitegeist.Bitzer:TestingAgent"                                                                      |
      | properties     | {"description":"task description"}                                                                   |
      | object         | {"nodeAggregateIdentifier":"i-do-not-exist", "workspaceName":"me-neither", "dimensionSpacePoint":{}} |
    Then I expect the constraint check result to contain an exception of type "ObjectDoesNotExist" at path "object"
    And I expect the task "tasky-mc-taskface" not to exist
