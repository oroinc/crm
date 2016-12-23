@skip
# todo: Should be enabled in CRM-6722
Feature: Import opportunity feature
  In order to simplify work with opportunities
  As crm user
  I want to import/export opportunities data

  Scenario: Data Template for Opportunity
    Given I login as administrator
    And "First Sales Channel" is a channel with enabled Business Customer entities
    And I open Opportunity Index page
    And there is no records in grid
    When I download Data Template file
    Then I don't see Business Customer Name column
    And I see Account Customer name column

  Scenario: Import Opportunity with Account and Customer
    Given crm has Acme Account with Charlie and Samantha customers
    And I fill template with data:
      | Account Customer name | Opportunity name | Status Id   |
      | Charlie               | Opportunity one  | in_progress |
      | Samantha              | Opportunity two  | in_progress |
    When I import file
    Then Charlie customer has Opportunity one opportunity
    And Samantha customer has Opportunity two opportunity
    And open Opportunity Index page
    And number of records should be 2
    And I should see Opportunity one in grid with following data:
      | Status                | Open      |
      | Owner                 | John Doe  |
#    @todo Uncomment when CRM-6490 will resolved
#      | Probability | 5%                  |

  Scenario: Import Opportunity with custom probability value
    Given I fill template with data:
      | Account Customer name | Opportunity name            | Status Id   | Probability |
      | Charlie               | Propose Dex Dogtective role | in_progress | 0.32        |
    When I import file
    # @todo remove "And I reload the page" when CRM-6492 will resolved
    And I reload the page
    Then I should see Propose Dex Dogtective role in grid with following data:
#      | Channel     | First Sales Channel |
      | Status      | Open                |
      | Owner       | John Doe            |
      | Probability | 32%                 |

  Scenario: Import Opportunity with new Account
    Given I fill template with data:
      | Account Customer name | Opportunity name  | Status Id   |
      | Absolute new account  | Opportunity three | in_progress |
    When I import file
    Then "Absolute new account" Account was created
    Then "Absolute new account" Customer was created
    And Absolute new account customer has Opportunity three opportunity

  Scenario: Import Opportunity with new Customer
    Given the following Account:
      | Name                     | Owner   | organization  |
      | Account without Customer | @admin  | @organization |
    And I fill template with data:
      | Account Customer name    | Opportunity name  | Status Id   |
      | Account without Customer | Opportunity four  | in_progress |
    When I open Opportunity Index page
    And import file
    Then "Account without Customer" Customer was created
    And Account without Customer customer has Opportunity four opportunity

  @skip
  # todo: CRM-6953
  Scenario: Import Opportunity with no Account
    Given I open Opportunity Index page
    And I fill template with data:
      | Channel Name        | Opportunity name  | Status Id   |
      | First Sales Channel | Opportunity five  | in_progress |
    When I try import file
    Then I should see validation message "Error in row #1. Account Customer name: This value should not be blank."
    And close ui dialog

  Scenario: Import Opportunity with
    Given I open Opportunity Index page
    And I fill template with data:
      | Account Customer name |
      | Acme                  |
    When I try import file
    Then I should see validation message "Error in row #1. Opportunity name: This value should not be blank."
    And I should see validation message "Error in row #1. Status Id: This value should not be blank."
