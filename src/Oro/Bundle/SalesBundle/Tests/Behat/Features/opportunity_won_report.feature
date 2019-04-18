@ticket-BAP-18621

Feature: Opportunity won report
  In order to show opportunity report
  As a administrator
  I need to be able filter opportunities by created date

  Scenario: Check that opportunity is shown by date filter with today value
    Given I login as administrator
    And I open Opportunity Create page
    And I fill "Opportunity Form" with:
      | Opportunity Name       | Supper Opportunity      |
      | Account                | customer                |
      | Contact                | CatherineJH@armyspy.com |
      | Status                 | Closed won              |
      | Close Reason           | Won                     |
      | Close Revenue Amount   | 100                     |
      | Close Revenue Currency | $                       |
    And click on "Select Expected Close Date"
    And click on "Today"
    And save and close form
    Then I should see "Opportunity saved" flash message
    And I go to Reports & Segments/ Reports/ Opportunities/ Won Opportunities By Period
    When I filter Created Date as equals "today" as single value
    And I should see following grid:
      | Number won | Close revenue |
      | 1          | $100.00       |
