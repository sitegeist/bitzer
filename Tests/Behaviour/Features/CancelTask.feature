@fixtures
Feature: Schedule task

  As an administrator of Bitzer I want to be able to cancel a task

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

  Scenario: Try to cancel a non-existing task
    When the command CancelTask is executed with payload and exceptions are caught:
      | Key            | Value            |
      | taskIdentifier | "i-do-not-exist" |
    Then the last command should have thrown an exception of type "TaskDoesNotExist"

  Scenario: Cancel an existing task
    When the command CancelTask is executed with payload:
      | Key            | Value               |
      | taskIdentifier | "tasky-mc-taskface" |
    Then I expect the task "tasky-mc-taskface" not to exist
