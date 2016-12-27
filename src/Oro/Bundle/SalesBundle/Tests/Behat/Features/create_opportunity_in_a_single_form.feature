Feature: Create opportunity in a single form
  I order to create Opportunity
  As a Sales rep
  I want to create Opportunity in a single form with minimum requirements

  Scenario: Required fields
    Given I login as administrator
    And "First Sales Channel" is a channel with enabled Business Customer entity
    And two users charlie and samantha exists in the system
    And they has their own Accounts and Customers
    And user have "User" permissions for "View" "Business Customer" entity
    And I open Opportunity Create page
    Then Opportunity Name is a required field
    And press "Cancel"

  @not-automated
  Scenario: Renamed fields
    Given I open Opportunity Create page
    When Close Date is renamed to Expected Close Date
    Then Customer Need and Proposed Solution have WYSIWYG editor

  @skip
  # @todo: CRM-7282. fix this test with new customer association flow
  Scenario: New Opportunity
    Given I press "Create Opportunity"
    When fill form with:
      | Opportunity Name | Supper Opportunity                |
      | Channel          | First Sales Channel               |
      | Account          | Diana Bailey (Samantha Account 2) |
    And save and close form
    Then I should see "Opportunity saved" flash message

  @skip
  # @todo: CRM-7282. fix this test with new customer association flow
  Scenario: New Account
    Given I open Opportunity creation page
    When fill form with:
      | Opportunity Name | Another New Opportunity |
      | Channel          | First Sales Channel     |
    And I select "Supper Brand New Customer Account"
    And save and close form
    Then I should see "Opportunity saved" flash message
    And "Supper Brand New Customer Account" Account was created

  @skip
  # @todo: CRM-7282. fix this test with new customer association flow
  Scenario: No permissions to create Account
    Given user permissions on Create Account is set to None
    And I open Opportunity creation page
    When I fill in "Channel" with "First Sales Channel"
    And type "Non Existent Account" into Account field
    Then I should see only existing accounts
    But should not see "Non Existent Account (Add new)" account
    And press "Cancel"
