Feature: Create opportunity in a single form
  I order to create Opportunity
  As a Sales rep
  I want to create Opportunity in a single form with minimum requirements

  Scenario: Required fields
    Given I login as administrator
    And "First Sales Channel" is a channel with enabled Business Customer entities
    And two users charlie and samantha exists in the system
    And user have "User" permissions for "View" "Business Customer" entity
    And they has their own Accounts and Business Customers
    And I open Opportunity Create page
    Then Opportunity Name is a required field
    And press "Cancel"

#  @skip
#  ToDo: uncomment when BAP-10673 will completed
#  Scenario: Renamed fields
#    Given I am on Opportunity index page
#    When I open Opportunity creation page
#    Then Close Date is renamed to Expected Close Date
#    And Customer Need and Proposed Solution have WYSIWYG editor

#todo: Uncomment, update in ticket "CRM-6333" where it will be possible to create new account using the field
#  Scenario: New Opportunity
#    Given I press "Create Opportunity"
#    When fill form with:
#      | Opportunity Name | Supper Opportunity                |
#      | Channel          | First Sales Channel               |
#      | Account          | Diana Bailey (Samantha Account 2) |
#    And save and close form
#    Then I should see "Opportunity saved" flash message
#
#  Scenario: New Account
#    Given I open Opportunity creation page
#    When fill form with:
#      | Opportunity Name | Another New Opportunity |
#      | Channel          | First Sales Channel     |
#    And I select "Supper Brand New Customer Account"
#    And save and close form
#    Then I should see "Opportunity saved" flash message
#    And "Supper Brand New Customer Account" Customer was created
#    And "Supper Brand New Customer Account" Account was created
#
#  Scenario: No permissions to create Account
#    Given user permissions on Create Account is set to None
#    And I open Opportunity creation page
#    When I fill in "Channel" with "First Sales Channel"
#    And type "Non Existent Account" into Account field
#    Then I should see only existing accounts
#    But should not see "Non Existent Account (Add new)" account
#    And press "Cancel"
#
#  Scenario: No permissions to create Business Customer
#    Given user permissions on Create Account is set to Global
#    And user permissions on Create Business Customer is set to None
#    When I open Opportunity creation page
#    When I fill in "Channel" with "First Sales Channel"
#    And type "Non Existent Account" into Account field
#    Then I should see only existing accounts
#    But should not see "Non Existent Account (Add new)" account
