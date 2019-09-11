@fixtures
Feature: Schedule task

  As a user of Bitzer I want to be able to mark a task as completed

  Background:
    Given I have the following additional agents:
    """
    'Sitegeist.Bitzer:TestingAgent':
      parentRoles: ['Sitegeist.Bitzer:Agent']
    'Sitegeist.Bitzer:TestingAdministrator':
      parentRoles: ['Sitegeist.Bitzer:Administrator']
    """
    And the command ScheduleTask is executed with payload:
      | Key            | Value                                                   |
      | taskIdentifier | "tasky-mc-taskface"                                     |
      | taskClassName  | "Sitegeist\\Bitzer\\Domain\\Task\\Generic\\GenericTask" |
      | scheduledTime  | "2020-01-02T00:00:00+00:00"                             |
      | agent          | "Sitegeist.Bitzer:TestingAgent"                         |
      | properties     | {"description":"task description"}                      |

  Scenario: Try to complete a non-existing task
    When the command CompleteTask is executed with payload and exceptions are caught:
      | Key            | Value            |
      | taskIdentifier | "i-do-not-exist" |
    Then the last command should have thrown an exception of type "TaskDoesNotExist"

  Scenario: Try to complete a non-existing task using a constraint check result
    Given exceptions are collected in a constraint check result
    When the command CompleteTask is executed with payload:
      | Key            | Value                                   |
      | taskIdentifier | "i-do-not-exist"                        |
    Then I expect the constraint check result to contain an exception of type "TaskDoesNotExist" at path "identifier"

  Scenario: Complete an existing task
    When the command CompleteTask is executed with payload:
      | Key            | Value               |
      | taskIdentifier | "tasky-mc-taskface" |
    Then I expect the task "tasky-mc-taskface" to exist
    And I expect this task to have action status "https://schema.org/CompletedActionStatus"
