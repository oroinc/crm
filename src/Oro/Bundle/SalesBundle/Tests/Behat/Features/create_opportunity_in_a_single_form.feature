Feature: Create opportunity in a single form
  I order to create Opportunity
  As a Sales rep
  I want to create Opportunity in a single form with minimum requirements

  Scenario: Required fields
    Given I login as administrator
    And "First Sales Channel" is a channel with enabled Business Customer entity
    When I open Opportunity Create page
    Then Opportunity Name is a required field
    And press "Cancel"

  @not-automated
  Scenario: Renamed fields
    Given I open Opportunity Create page
    When Close Date is renamed to Expected Close Date
    Then Customer Need and Proposed Solution have WYSIWYG editor

  Scenario: New Opportunity
    Given there is following Account:
      | name |
      | Acme |
    And "Acme" Account was created
    And there is one record in grid
    When I open Opportunity Create page
    And fill form with:
      | Opportunity Name | Supper Opportunity |
      | Account          | Acme               |
    And save and close form
    Then I should see "Opportunity saved" flash message

  Scenario: New Account
    Given I open Opportunity Create page
    When fill form with:
      | Opportunity Name | Another New Opportunity           |
      | Account          | Supper Brand New Customer Account |
    And save and close form
    Then I should see "Opportunity saved" flash message
    But "Supper Brand New Customer Account" Account was created
    And there are two records in grid

  Scenario: No permissions to create Account
    Given user permissions on Create Account is set to None
    And I open Opportunity Create page
    And type "Non Existent Account" into Account field
    Then I should see only existing accounts
    And press "Cancel"
