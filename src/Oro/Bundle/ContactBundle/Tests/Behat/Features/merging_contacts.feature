@fixture-OroContactBundle:merge_contacts.yml

Feature: Merging contacts
  In order to manage contacts
  As an administrator
  I need to have mass merge action available for contacts

  Scenario: Merge 1 contact validation
    Given I login as administrator
    And I go to Customers/Contacts
    And number of records should be 6
    And I check First_1 record in grid
    When I click "Merge" link from mass action dropdown
    Then I should see "Select from 2 to 5 records." flash message

  Scenario: Merge 6 contacts validation
    Given I check records in grid:
      | First_2 |
      | First_3 |
      | First_4 |
      | First_5 |
      | First_6 |
    When I click "Merge" link from mass action dropdown
    Then I should see "Too many records selected. Merge supports maximum 5 records." flash message

  Scenario: Merge 2 contacts
    Given I go to Customers/Contacts
    And number of records should be 6
    When I check first 2 records in grid
    And I click "Merge" link from mass action dropdown
    And I click "Merge"
    Then I should see "Entities were successfully merged" flash message
    And I should see "5556668881"
    And I should see "test1@test.com"
    And I should see "TestContact1 TestContact1"
    And I should see "5556668882"
    And I should see "test2@test.com"
    And I should see "TestContact2 TestContact2"

  Scenario: Merge 5 contacts
    Given I go to Customers/Contacts
    And number of records should be 5
    And I sort grid by "Email"
    When I check all records in grid
    And I click "Merge" link from mass action dropdown
    And I click "Merge"
    Then I should see "Entities were successfully merged" flash message
    And I go to Customers/Contacts
    And number of records should be 1
