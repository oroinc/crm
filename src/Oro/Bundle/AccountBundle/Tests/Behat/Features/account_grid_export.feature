@fix-BAP-15754
@fixture-OroAccountBundle:account-grid-export.yml
Feature: Account grid export
  In order to get account grid data in text format
  As an Administrator
  I want to be able to export account via Management Console UI

  Scenario: Should be possible to export grid ordered by contact name column
    Given I login as administrator
    And I go to Customers / Accounts
    And I should see following records in grid:
      | First_1 Last_1 |
      | First_2 Last_2 |
      | First_3 Last_3 |
      | First_4 Last_4 |
      | First_5 Last_5 |
    When I sort grid by "Contact name"
    And I click "Export Grid"
    And I click "CSV"
    Then I should see "Export started successfully. You will receive email notification upon completion."
    And Email should contains the following "Grid export performed successfully. Download" text
