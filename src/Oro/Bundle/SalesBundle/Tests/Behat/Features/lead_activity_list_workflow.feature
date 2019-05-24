@ticket-BB-16759
@fixture-OroSalesBundle:LeadFixture.yml
Feature: Lead activity list workflow
  In order to manage activities for Lead entity
  As an administrator
  I should perform workflow transitions for using actions for activity list item

  Scenario: Create task for lead and check that workflow actions do transition successfully
    Given I login as administrator
    And I should see Sales/Leads in main menu
    And I go to Sales/Leads
    And I click View Lead 1 in grid
    When click "More actions"
    And click "Add task"
    And fill form with:
      | Subject | Test task |
    And click "Create Task"
    Then should see "Task created successfully" flash message

    When I click "Start progress" on "Test task" in activity list
    Then shouldn't see "Test task Open" task in activity list
    And should see "Test task In progress" task in activity list

    When I click "Close" on "Test task" in activity list
    Then shouldn't see "Test task In progress" task in activity list
    And should see "Test task Closed" task in activity list
