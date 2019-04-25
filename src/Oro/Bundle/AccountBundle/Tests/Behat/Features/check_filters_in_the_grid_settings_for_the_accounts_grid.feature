@regression
@ticket-@BB-16554
@fixture-OroContactBundle:LoadContactEntitiesFixture.yml
@fixture-OroAccountBundle:LoadAccountEntitiesFixture.yml
Feature: Check filters in the Grid settings for the Accounts grid
  In order to operate with accounts grid filters
  As an Administrator
  I should see appropriate list of filters in filter manager

  Scenario: Check available filters on accounts grid
    Given I login as administrator
    And I go to Customers/ Accounts
    When click "Grid Settings"
    And I click "Filters" tab
    Then I should see following filters in the grid settings in exact order:
      | Account Name  |
      | Contact Name  |
      | Contact Email |
      | Contact Phone |
      | Owner         |
      | Business Unit |
      | Created At    |
      | Updated At    |
      | Tags          |
