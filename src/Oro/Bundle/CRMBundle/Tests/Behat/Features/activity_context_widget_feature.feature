@ticket-BAP-14557
@regression
@fixture-OroCRMBundle:activities-contexts.yml
Feature: Activity context widget feature
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
    And select "Account" context
    And click on Test Account in grid
    Then I should see "Test Account"

    When I click "Add Context"
    And I select "Lead" context
    And click on Test Lead in grid
    Then I should see "Test Lead"

    When I click "Add Context"
    And I select "Opportunity" context
    And click on Test Opportunity in grid
    Then I should see "Test Opportunity"

  Scenario: Not showing context activities in task view page with disabled permission
    Given administrator permissions on View Lead is set to None
    When I reload the page
    Then I should see "Test Account"
    And I should not see "Test Lead"
    And I should see "Test Opportunity"

  Scenario: Not showing context activities in tasks grid
    When I go to Activities/Tasks
    Then should see following grid:
      | Subject   | Contexts |
      | Test task | Test Account Test Opportunity |

  Scenario: Not showing context activities in task edit page
    When I click Edit Test task in grid
    Then I should see "Test Account"
    And I should not see "Test Lead"
    And I should see "Test Opportunity"

  Scenario: Inaccessible context activities remains after save
    When I save and close form
    Then should see "Task saved" flash message
    Then I should see "Test Account"
    And I should not see "Test Lead"
    And I should see "Test Opportunity"
    When administrator permissions on View Lead is set to Global
    And I reload the page
    Then I should see "Test Account"
    And I should see "Test Lead"
    And I should see "Test Opportunity"
