@regression
@fixture-OroUserBundle:samantha_and_charlie_users.yml
@fixture-OroSalesBundle:accounts_with_customers.yml

Feature: Import opportunity
  In order to simplify work with opportunities
  As crm user
  I want to import/export opportunities data

  Scenario: Data Template for Opportunity
    Given I login as administrator
    And I go to Sales/ Opportunities
    And there is no records in grid
    When I download "Opportunity" Data Template file
    Then I don't see Business Customer Name column
    And I see Customer Account Account name column

  Scenario: Import Opportunity with Accounts
    Given I fill template with data:
      | Customer Account Account name | Opportunity name  | Status Id   | Probability | Channel Name       |
      | Charlie                       | Opportunity one   | in_progress | 5           | Business Customers |
      | Samantha                      | Opportunity two   | in_progress | 5           | Business Customers |
      | Absolute new account          | Opportunity three | in_progress | 0           | Business Customers |
    When I import file
    And I reload the page
    Then number of records should be 3
    And I should see Opportunity one in grid with following data:
      | Status      | Open     |
      | Owner       | John Doe |
      | Probability | 5%       |
    And I should see Opportunity two in grid with following data:
      | Status      | Open     |
      | Owner       | John Doe |
      | Probability | 5%       |
    And I should see Opportunity three in grid with following data:
      | Status      | Open     |
      | Owner       | John Doe |
      | Probability | 0%       |
    And "Absolute new account" Account was created
