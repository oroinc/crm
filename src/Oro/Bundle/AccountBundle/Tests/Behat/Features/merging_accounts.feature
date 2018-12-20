@fixture-OroAccountBundle:merge_accounts.yml
@ticket-@BB-12444
@ticket-@CRM-9023
Feature: Merging accounts
  In order to manage accounts
  As administrator
  I need to have mass merge action available for accounts with activity

  Scenario: Merge 1 accounts validation
    Given I login as administrator
    And I go to Customers/Accounts
    And number of records should be 6
    And I check Account_1 record in grid
    When I click "Merge" link from mass action dropdown
    Then I should see "Select from 2 to 5 records." flash message

  Scenario: Merge 6 accounts validation
    Given I check records in grid:
      | Account_2 |
      | Account_3 |
      | Account_4 |
      | Account_5 |
      | Account_6 |
    When I click "Merge" link from mass action dropdown
    Then I should see "Too many records selected. Merge supports maximum 5 records." flash message

  Scenario: Merge 2 accounts
    Given I go to Customers/Accounts
    And number of records should be 6
    When I check first 2 records in grid
    And I click "Merge" link from mass action dropdown
    And I click "Merge"
    Then I should see "Entities were successfully merged" flash message
    And I should see "First_1 Last_1"
    And I should see "First_2 Last_2"

  Scenario: Merge 5 accounts
    Given I go to Customers/Accounts
    And number of records should be 5
    When I check all records in grid
    And I click "Merge" link from mass action dropdown
    And I click "Merge"
    Then I should see "Entities were successfully merged" flash message
    And I go to Customers/Accounts
    And number of records should be 1
