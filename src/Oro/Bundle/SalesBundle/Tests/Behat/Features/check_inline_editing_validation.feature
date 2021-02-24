@ticket-BAP-20446

Feature: Check inline editing validation
  In order to be sure inline editing validation works correctly
  As a administrator
  I want to see a validation message when the user enters an invalid value 

  Scenario: Check validation message
    Given I login as administrator
    And I open Opportunity Create page
    And I fill "Opportunity Form" with:
      | Opportunity Name       | Opportunity 1           |
      | Account                | customer                |
      | Probability (%)        | 1                       |
    And save and close form
    And I should see "Opportunity saved" flash message
    And I go to Sales/ Opportunities
    And I reload the page
    And I should see Opportunity 1 in grid
    When I edit "1" Probability as "101" without saving
    And I click "Save changes"
    Then I should see "This value should be 100 or less."
