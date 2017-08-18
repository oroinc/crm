@fix-BAP-15111
Feature: Custom field deleted via UI
  In order to keep users interface clear and straight forward
  As an Administrator
  I want to have possibility to delete custom fields which are not used anymore

  Scenario: Create String field for Account entity
    Given I login as administrator
    And I have Opportunity connected to Account
    When I go to System/ Entities/ Entity Management
    And I filter Name as is equal to "Account"
    And I click "View" on row "Account" in grid
    And I click "Create field"
    And I fill form with:
      | Field name   | TestField    |
      | Storage type | Table column |
      | Type         | String       |
    And I click "Continue"
    And I save and close form
    Then I should see "Field saved" flash message
    When I click update schema
    Then I should see Schema updated flash message

  Scenario: Delete unused field for Account entity
   And I go to System/ Entities/ Entity Management
    When I filter Name as is equal to "Account"
    And I click "View" on row "Account" in grid
    And I click "Remove" on row "TestField" in grid
    And I click "Yes" in confirmation dialogue
    And I click update schema
    Then I should see Schema updated flash message

  Scenario: Check that I can open Opportunity entity
    Given I go to Sales/ Opportunities
    When I click "View" on row "Opportunity 1" in grid
    Then I should see "Opportunity 1"
