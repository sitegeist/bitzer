@fixtures
Feature: Schedule task

  As a user of Bitzer I want to be able to reschedule a task to another time

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

  Scenario: Try to reschedule a non-existing task
    When the command RescheduleTask is executed with payload and exceptions are caught:
      | Key            | Value                       |
      | taskIdentifier | "i-do-not-exist"            |
      | scheduledTime  | "2021-01-02T00:00:00+00:00" |
    Then the last command should have thrown an exception of type "TaskDoesNotExist"

  Scenario: Try to reschedule a non-existing task using a constraint check result
    Given exceptions are collected in a constraint check result
    When the command RescheduleTask is executed with payload:
      | Key            | Value                       |
      | taskIdentifier | "i-do-not-exist"            |
      | scheduledTime  | "2021-01-02T00:00:00+00:00" |
    Then I expect the constraint check result to contain an exception of type "TaskDoesNotExist" at path "identifier"

  Scenario: Try to reschedule an existing task without a valid time
    When the command RescheduleTask is executed with payload and exceptions are caught:
      | Key            | Value               |
      | taskIdentifier | "tasky-mc-taskface" |
    Then the last command should have thrown an exception of type "ScheduledTimeIsUndefined"

  Scenario: Try to reschedule an existing task without a valid time using a constraint check result
    Given exceptions are collected in a constraint check result
    When the command RescheduleTask is executed with payload:
      | Key            | Value               |
      | taskIdentifier | "tasky-mc-taskface" |
    Then I expect the constraint check result to contain an exception of type "ScheduledTimeIsUndefined" at path "scheduledTime"
    And I expect the task "tasky-mc-taskface" to exist
    And I expect this task to be scheduled to "2020-01-02T00:00:00+00:00"

  Scenario: Reschedule an existing task
    When the command RescheduleTask is executed with payload:
      | Key            | Value                       |
      | taskIdentifier | "tasky-mc-taskface"         |
      | scheduledTime  | "2021-01-02T00:00:00+00:00" |
    Then I expect the task "tasky-mc-taskface" to exist
    And I expect this task to be scheduled to "2021-01-02T00:00:00+00:00"
