@fixtures
Feature: Schedule task

  As a user of Bitzer I want to be able to set a new target for a task

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
      | target         | "https://www.neos.io"                                   |

  Scenario: Try to set a new target for a non-existing task
    When the command SetNewTaskTarget is executed with payload and exceptions are caught:
      | Key            | Value                  |
      | taskIdentifier | "i-do-not-exist"       |
      | target         | "https://docs.neos.io" |
    Then the last command should have thrown an exception of type "TaskDoesNotExist"

  Scenario: Try to set a new target for a non-existing task using a constraint check result
    Given exceptions are collected in a constraint check result
    When the command SetNewTaskTarget is executed with payload:
      | Key            | Value                  |
      | taskIdentifier | "i-do-not-exist"       |
      | target         | "https://docs.neos.io" |
    Then I expect the constraint check result to contain an exception of type "TaskDoesNotExist" at path "identifier"

  Scenario: Try to set a new invalid target
    When the command SetNewTaskTarget is executed with payload and exceptions are caught:
      | Key            | Value               |
      | taskIdentifier | "tasky-mc-taskface" |
      | target         | "i-am-not-a-uri"    |
    Then the last command should have thrown an exception of type "TargetIsInvalid"

  Scenario: Try to set a new invalid target using a constraint check result
    Given exceptions are collected in a constraint check result
    When the command SetNewTaskTarget is executed with payload:
      | Key            | Value                  |
      | taskIdentifier | "tasky-mc-taskface" |
      | target         | "i-am-not-a-uri"    |
    Then I expect the constraint check result to contain an exception of type "TargetIsInvalid" at path "target"
    And I expect the task "tasky-mc-taskface" to exist
    And I expect this task to have the target "https://www.neos.io"

  Scenario: Set a new valid target to an existing task
    When the command SetNewTaskTarget is executed with payload:
      | Key            | Value                  |
      | taskIdentifier | "tasky-mc-taskface"    |
      | target         | "https://docs.neos.io" |
    Then I expect the task "tasky-mc-taskface" to exist
    And I expect this task to have the target "https://docs.neos.io"
