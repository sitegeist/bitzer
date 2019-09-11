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

  Scenario: Schedule a minimal generic task
    When the command ScheduleTask is executed with payload:
      | Key            | Value                                                   |
      | taskIdentifier | "tasky-mc-taskface"                                     |
      | taskClassName  | "Sitegeist\\Bitzer\\Domain\\Task\\Generic\\GenericTask" |
      | scheduledTime  | "2020-01-02T00:00:00+00:00"                             |
      | agent          | "Sitegeist.Bitzer:TestingAgent"                         |
      | properties     | {"description":"task description"}                      |
    Then I expect the task "tasky-mc-taskface" to exist
    And I expect this task to be of class "Sitegeist\Bitzer\Domain\Task\Generic\GenericTask"
    And I expect this task to have action status "https://schema.org/PotentialActionStatus"
    And I expect this task to be scheduled to "2020-01-02T00:00:00+00:00"
    And I expect this task to be assigned to "Sitegeist.Bitzer:TestingAgent"
    And I expect this task to have the properties:
      | Key         | Value            |
      | description | task description |

  Scenario: Schedule a complete generic task
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
      | Identifier       | Path                    | Node Type                         | Properties        | Workspace |
      | sites            | /sites                  | unstructured                      | {}                | live      |
      | nody-mc-nodeface | /sites/sity-mc-siteface | Sitegeist.Bitzer:Testing.Document | {"title": "Home"} | live      |
    When the command ScheduleTask is executed with payload:
      | Key            | Value                                                                                            |
      | taskIdentifier | "tasky-mc-taskface"                                                                              |
      | taskClassName  | "Sitegeist\\Bitzer\\Domain\\Task\\Generic\\GenericTask"                                          |
      | scheduledTime  | "2020-01-02T00:00:00+00:00"                                                                      |
      | agent          | "Sitegeist.Bitzer:TestingAgent"                                                                  |
      | object         | {"nodeAggregateIdentifier":"nody-mc-nodeface", "workspaceName":"live", "dimensionSpacePoint":{}} |
      | target         | "https://www.neos.io/"                                                                           |
      | properties     | {"description":"read the manual", "hint":"the cake is a lie"}                                    |
    Then I expect the task "tasky-mc-taskface" to exist
    And I expect this task to be of class "Sitegeist\Bitzer\Domain\Task\Generic\GenericTask"
    And I expect this task to have action status "https://schema.org/PotentialActionStatus"
    And I expect this task to be scheduled to "2020-01-02T00:00:00+00:00"
    And I expect this task to be assigned to "Sitegeist.Bitzer:TestingAgent"
    And I expect this task to be about '{"nodeAggregateIdentifier":"nody-mc-nodeface", "workspaceName":"live", "dimensionSpacePoint":{}}'
    And I expect this task to have the properties:
      | Key         | Value             |
      | description | read the manual   |
      | hint        | the cake is a lie |
