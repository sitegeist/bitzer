@fixtures
Feature: Schedule task

  As a user of Bitzer I want to be able to reassign a task to another agent

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

  Scenario: Try to reassign a non-existing task
    When the command ReassignTask is executed with payload and exceptions are caught:
      | Key            | Value                                   |
      | taskIdentifier | "i-do-not-exist"                        |
      | agent          | "Sitegeist.Bitzer:TestingAdministrator" |
    Then the last command should have thrown an exception of type "TaskDoesNotExist"

  Scenario: Try to reassign a non-existing task using a constraint check result
    Given exceptions are collected in a constraint check result
    When the command ReassignTask is executed with payload:
      | Key            | Value                                   |
      | taskIdentifier | "i-do-not-exist"                        |
      | agent          | "Sitegeist.Bitzer:TestingAdministrator" |
    Then I expect the constraint check result to contain an exception of type "TaskDoesNotExist" at path "identifier"

  Scenario: Try to reassign an existing task to a non-existing agent
    When the command ReassignTask is executed with payload and exceptions are caught:
      | Key            | Value               |
      | taskIdentifier | "tasky-mc-taskface" |
      | agent          | "I.Do:NotExist"     |
    Then the last command should have thrown an exception of type "AgentDoesNotExist"

  Scenario: Try to reassign an existing task to a non-existing agent using a constraint check result
    Given exceptions are collected in a constraint check result
    When the command ReassignTask is executed with payload:
      | Key            | Value               |
      | taskIdentifier | "tasky-mc-taskface" |
      | agent          | "I.Do:NotExist"     |
    Then I expect the constraint check result to contain an exception of type "AgentDoesNotExist" at path "agent"
    And I expect the task "tasky-mc-taskface" to exist
    And I expect this task to be assigned to "Sitegeist.Bitzer:TestingAgent"

  Scenario: Reassign an existing task
    When the command ReassignTask is executed with payload:
      | Key            | Value                                   |
      | taskIdentifier | "tasky-mc-taskface"                     |
      | agent          | "Sitegeist.Bitzer:TestingAdministrator" |
    Then I expect the task "tasky-mc-taskface" to exist
    And I expect this task to be assigned to "Sitegeist.Bitzer:TestingAdministrator"
