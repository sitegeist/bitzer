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

  Scenario: Try to schedule a task without a scheduled time
    When the command ScheduleTask is executed with payload and exceptions are caught:
      | Key            | Value                                                   |
      | taskIdentifier | "tasky-mc-taskface"                                     |
      | taskClassName  | "Sitegeist\\Bitzer\\Domain\\Task\\Generic\\GenericTask" |
      | agent          | "Sitegeist.Bitzer:TestingAgent"                         |
      | properties     | {"description":"task description"}                      |
    Then the last command should have thrown an exception of type "ScheduledTimeIsUndefined"

  Scenario: Try to schedule a task without a valid agent
    When the command ScheduleTask is executed with payload and exceptions are caught:
      | Key            | Value                                                   |
      | taskIdentifier | "tasky-mc-taskface"                                     |
      | taskClassName  | "Sitegeist\\Bitzer\\Domain\\Task\\Generic\\GenericTask" |
      | scheduledTime  | "2020-01-02T00:00:00+00:00"                             |
      | agent          | "I.Do:NotExist"                                         |
      | properties     | {"description":"task description"}                      |
    Then the last command should have thrown an exception of type "AgentDoesNotExist"

  Scenario: Try to schedule a task without a description
    When the command ScheduleTask is executed with payload and exceptions are caught:
      | Key            | Value                                                   |
      | taskIdentifier | "tasky-mc-taskface"                                     |
      | taskClassName  | "Sitegeist\\Bitzer\\Domain\\Task\\Generic\\GenericTask" |
      | scheduledTime  | "2020-01-02T00:00:00+00:00"                             |
      | agent          | "Sitegeist.Bitzer:TestingAgent"                         |
      | properties     | {}                                                      |
    Then the last command should have thrown an exception of type "DescriptionIsInvalid"

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
