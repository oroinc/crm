@ticket-BB-21810
@regression
@fixture-OroAccountBundle:import_accounts.yml
Feature: Import Accounts
  In order to add multiple accounts at once
  As an Administrator
  I want to be able to import accounts from a CSV file using a provided template

  Scenario: Data Template for Accounts
    Given I login as administrator
    And go to Customers/ Accounts
    And there is no records in grid
    When I download "Accounts" Data Template file
    Then I see Account name column
    And I see Default Contact First name column
    And I see Default Contact Last name column
    And I see Contacts 1 First name column
    And I see Contacts 1 Last name column
    And I see Contacts 2 First name column
    And I see Contacts 2 Last name column
    And I see Description column
    And I see Organization Name column
    And I see Referred By Account name column

  Scenario: Import new Accounts
    Given I fill template with data:
      | Id | Account name  | Default Contact First Name | Default Contact Last Name | Contacts 1 First name | Contacts 1 Last name | Contacts 2 First name | Contacts 2 Last name | Organization Name |
      |    | Account 1     | Account_FirstName_1        | Account_LastName_1        | Account_FirstName_1   | Account_LastName_1   |                       |                      | ORO               |
      |    | Account 2     | Account_FirstName_2        | Account_LastName_2        | Account_FirstName_2   | Account_LastName_2   | Account_FirstName_3   | Account_LastName_3   | ORO               |
    When I import file
    Then Email should contains the following "Errors: 0 processed: 2, read: 2, added: 2, updated: 0, replaced: 0" text
    When I reload the page
    Then I should see following grid:
      | Account name  | Contact Name                           | Contact email             |
      | Account 1     | Account_FirstName_1 Account_LastName_1 | contact_email_1@gmail.com |
      | Account 2     | Account_FirstName_2 Account_LastName_2 | contact_email_2@gmail.com |
    And number of records should be 2

  Scenario: Export - Import Customers
    Given I click "Export"
    Then I should see "Export started successfully. You will receive email notification upon completion." flash message
    And Email should contains the following "Export performed successfully. 2 accounts were exported. Download" text
    When I import exported file
    Then I should see "Import started successfully. You will receive an email notification upon completion." flash message
    And Email should contains the following "Errors: 0 processed: 2, read: 2, added: 0, updated: 0, replaced: 2" text
    And I reload the page
    And number of records should be 2
