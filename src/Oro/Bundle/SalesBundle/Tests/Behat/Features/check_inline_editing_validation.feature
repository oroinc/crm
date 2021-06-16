@ticket-BAP-20446

Feature: Check inline editing validation
  In order to be sure inline editing validation works correctly
  As an administrator
  I want to see a validation message when the user enters an invalid value 

  Scenario: Create an opportunity
    Given I login as administrator
    And I open Opportunity Create page
    When I fill "Opportunity Form" with:
      | Opportunity Name | Opportunity 1 |
      | Account          | customer      |
      | Probability (%)  | 1             |
    And save and close form
    Then I should see "Opportunity saved" flash message

  Scenario: Check validation message
    Given I go to Sales/ Opportunities
    And I reload the page
    And I should see Opportunity 1 in grid
    When I edit "1" Probability as "101" without saving
    And I click "Save changes"
    Then I should see "This value should be between 0 and 100."
