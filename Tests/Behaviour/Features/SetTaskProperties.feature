@fixtures
Feature: Set task properties

  As a user of Bitzer I want to be able to set arbitrary custom properties of a task

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

  Scenario: Try to set new properties for a non-existing task
    When the command SetTaskProperties is executed with payload and exceptions are caught:
      | Key            | Value                                      |
      | taskIdentifier | "i-do-not-exist"                           |
      | properties     | {"description":"changed task description"} |
    Then the last command should have thrown an exception of type "TaskDoesNotExist"

  Scenario: Try to set new properties for a non-existing task using a constraint check result
    Given exceptions are collected in a constraint check result
    When the command SetNewTaskTarget is executed with payload:
      | Key            | Value                                      |
      | taskIdentifier | "i-do-not-exist"                           |
      | properties     | {"description":"changed task description"} |
    Then I expect the constraint check result to contain an exception of type "TaskDoesNotExist" at path "identifier"

  Scenario: Try to set new properties without description for an existing task
    When the command SetTaskProperties is executed with payload and exceptions are caught:
      | Key            | Value                        |
      | taskIdentifier | "tasky-mc-taskface"          |
      | properties     | {"hint":"the cake is a lie"} |
    Then the last command should have thrown an exception of type "DescriptionIsInvalid"

  Scenario: Try to set new properties without description for an existing task using a constraint check result
    Given exceptions are collected in a constraint check result
    When the command SetTaskProperties is executed with payload:
      | Key            | Value                        |
      | taskIdentifier | "tasky-mc-taskface"          |
      | properties     | {"hint":"the cake is a lie"} |
    Then I expect the constraint check result to contain an exception of type "DescriptionIsInvalid" at path "properties.description"
    And I expect the task "tasky-mc-taskface" to exist
    And I expect this task to have the properties:
      | Key         | Value            |
      | description | task description |

  Scenario: Set new properties for an existing task
    When the command SetTaskProperties is executed with payload:
      | Key            | Value                                      |
      | taskIdentifier | "tasky-mc-taskface"                        |
      | properties     | {"description":"changed task description"} |
    Then I expect the task "tasky-mc-taskface" to exist
    And I expect this task to have the properties:
      | Key         | Value                    |
      | description | changed task description |
