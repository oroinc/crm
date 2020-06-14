@ticket-BAP-16828

Feature: Lead report by date
  In order to show lead report
  As a administrator
  I need to be able filter leads by created date

  Scenario: Check that lead is shown by date filter
    Given I login as administrator
    And I open Lead Create page
    And I fill "Lead Form" with:
      | Lead Name | John Doe |
    And save and close form
    Then I should see "Lead saved" flash message
    And I go to Reports & Segments/ Reports/ Leads/ Leads By Date
    When I filter Created Date as between "today" and "today + 1"
    And I should see following grid:
      | Leads count |
      | 1           |
