Feature: Create opportunity in a single form
  I order to create Opportunity
  As a Sales rep
  I want to create Opportunity in a single form with minimum requirements

  Scenario: Reaquired fields
    Given I login as administrator
    And "First Sales Channel" is a channel with enabled Opportunity, Lead, Business Customer entities
    And two users charlie and samantha exists in the system
    And user have "User" permissions for "View" "Business Customer" entity
    And they has their own Accounts and Business Customers
    And go to Sales/ Opportunities
    When I press "Create Opportunity"
    Then Opportunity Name is a required field
    And Account is a required field
    And press "Cancel"

#  Scenario: Renamed fields
#    Given I am on Opportunity index page
#    When I open Opportunity creation page
#    Then Close Date is renamed to Expected Close Date
#    And Customer Need and Proposed Solution have WYSIWYG editor

  Scenario Outline: Create Opportunity with 1 'sales channel'
    Given I login as "<user>" user
    And go to Sales/ Opportunities
    When I press "Create Opportunity"
    And Accounts and Customers in the control are filtered according to <user> ACL permissions
    And press "Cancel"
    Examples:
      | user     |
      | charlie  |
      | samantha |

  Scenario: One more sales channel
    Given CRM has second sales channel with Accounts and Business Customers

  Scenario Outline: Create Opportunity with more than 1 'sales channel'
    Given I login as "<user>" user
    And go to Sales/ Opportunities
    When I press "Create Opportunity"
    Then Accounts and Customers in the control are filtered by selected sales channel and <user> ACL permissions
    And press "Cancel"

  Examples:
    | user     |
    | charlie  |
    | samantha |

  Scenario: New Opportunity
    Given I press "Create Opportunity"
    When fill form with:
      | Opportunity Name | Supper Opportunity                |
      | Channel          | First Sales Channel               |
      | Account          | Diana Bailey (Samantha Account 2) |
    And save and close form
    Then I should see "Opportunity saved" flash message

  Scenario: Account name is equal to Business Customer name
    Given I login as "samantha" user
    Given Account Name is equal to Business Customer name
    And go to Sales/ Opportunities
    When I press "Create Opportunity"
    And select "First Sales Channel" from "Channel"
    Then I see only Account name in Account/Customer field choice

#  Scenario: Account has no Business Customers
#    Given CRM has 'sales channels'
#    And Account has no customers
#    When I open Opportunity creation page
#    And I select Account
#    Then after the form is saved new Customer is created
#    And Customer name is equal to Account name
#    And new Opportunity is created
#
#  Scenario: New Account
#    Given CRM has 'sales channels'
#    When I open Opportunity creation page
#    And no Account with such name exists
#    Then after the form is saved new Account and Customer are created
#    And Customer name is equal to Account name
#    And new Opportunity is created
#
#  Scenario: No permissions to create Account
#    Given CRM has 'sales channels'
#    And my permission on Create Account is set to None
#    When I open Opportunity creation page
#    And no Account with such name exists
#    Then after the form is saved I see a warning message "You do not have permission to create Account"
#
#  Scenario: No permissions to create Business Customer
#    Given CRM has 'sales channels'
#    And my permission on Create Business Customer is set to None
#    When I open Opportunity creation page
#    And I select Account
#    And I press Save button
#    Then I see a warning message "You do not have permission to create Customer"
