@fixture-OroAccountBundle:merge_accounts.yml
@ticket-@BB-12444
Feature: Merging accounts
  In order to merge two accounts
  As administrator
  I need to have mass merge action available

  Scenario: Merge two accounts
    Given I login as administrator
    And I go to Customers/Accounts
    And I sort grid by "Contact Email"
    When I check all records in grid
    And I click "Merge" link from mass action dropdown
    And I click "Merge"
    Then I should see "Entities were successfully merged" flash message
    And I should see "First_1 Last_1"
    And I should see "First_2 Last_2"

