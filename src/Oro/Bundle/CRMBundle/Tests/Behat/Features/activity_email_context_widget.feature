@ticket-BAP-20028
@regression
@fixture-OroCRMBundle:activity-email-context.yml
Feature: Activity email context widget
  In order to have ability manage activities contexts
  As OroCRM sales rep
  I need to view available activities in context widget

  Scenario: Create task with context activities
    Given I login as administrator
    When I go to Activities/Tasks
    And click "Create Task"
    And fill form with:
      | Subject | Test task |
    And save and close form
    Then should see "Task saved" flash message

    When I click "Add Context"
    And I select "Email" context
    And click on Test Email in grid
    Then I should see "Test Email"
