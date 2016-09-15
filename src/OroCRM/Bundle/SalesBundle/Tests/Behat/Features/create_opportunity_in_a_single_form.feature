Feature: Create opportunity in a single form
  I order to create Opportunity
  As a Sales rep
  I want to create Opportunity in a single form with minimum requirements

  Scenario: Reaquired fields
    Given "sales channel" is a channel with enabled Opportunity, Lead, Business Customer entities
    And I login as administrator
    And go to Sales/ Opportunities
    When I press "Create Opportunity"
    Then Opportunity Name is a required field
    And Account is a required field

#  Scenario: Renamed fields
#    Given I am on Opportunity index page
#    When I open Opportunity creation page
#    Then Close Date is renamed to Expected Close Date
#    And Customer Need and Proposed Solution have WYSIWYG editor
#
#  Scenario: Create Opportunity with 1 'sales channel'
#    Given CRM has one 'sales channel'
#    And Account has Business Customers
#    When I open Opportunity creation page
#    Then Accounts in the control are filtered by 'sales channel'
#    And Accounts in the control are filtered according to my ACL permissions
#
#  Scenario: Create Opportunity with more than 1 'sales channel'
#    Given CRM has 'sales channels'
#    And Account has Business Customers
#    When I open Opportunity creation page page
#    Then Accounts in the control are filtered by selected 'sales channel'
#    And Customers in the control are filtered by Account
#    And Accounts and Customers in the control are filtered according to my ACL permissions
#
#  Scenario: New Opportunity
#    Given CRM has 'sales channels'
#    And Account has Business Customers
#    When I open Opportunity creation page
#    And select 'sales channel'
#    And I select Account/Customer
#    Then after the form is saved a new Opportunity is created
#
#  Scenario: Account name is equal to Business Customer name
#    Given CRM has 'sales channels'
#    And Account has one Business Customer
#    And Account Name is equal to Business Customer name
#    When I open Opportunity creation page
#    And select 'sales channel'
#    And I make a search by Account/Customer
#    Then in the result I see only Account name
#
#  Scenario: Account name is not equal to Business Customer name
#    Given CRM has 'sales channels'
#    And Account has one Business Customer
#    And Account Name is not equal to Business Customer name
#    When I open Opportunity creation page page
#    And select 'sales channel'
#    And I make a search by Account/Customer
#    Then in the result I see Account name and Customer name
#
#  Scenario: Account has more than one Business Customer
#    Given CRM has 'sales channels'
#    And Account has Business Customers
#    When I open Opportunity creation page
#    And select 'sales channel'
#    And I make a search by Account/Customer
#    Then in the result I see Account name and Customer name
#
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
