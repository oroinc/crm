@regression
@ticket-BAP-23515
@fixture-OroSalesBundle:opportunities_data.yml
Feature: Year range of expected close date picker for a future date
  In order to plan opportunities far ahead
  As an Administrator
  I want the expected close date picker to show the selected year and to reach months beyond the current year + 1

  Scenario: Manually typed future date keeps its year in the reopened picker
    Given I login as administrator
    And I go to Sales / Opportunities
    And I click edit "Opportunity 1" in grid
    When I type "Sep 29, 2045" in "Expected close date"
    And I click on empty space
    And I save and close form
    Then I should see "Opportunity saved" flash message
    When I go to Sales / Opportunities
    Then I should see Opportunity 1 in grid with following data:
      | EXPECTED CLOSE DATE | Sep 29, 2045 |
    When I click edit "Opportunity 1" in grid
    And I focus on "Expected close date" field
    Then I should see that option "2045" is selected in "DateTimePickerYearSelect" select
    And I should see "2065" for "DateTimePickerYearSelect" select
    And I should not see "1946" for "DateTimePickerYearSelect" select

  Scenario: Next month navigation beyond the current year + 1
    When I click on "DateTimePickerNextMonth"
    Then I should see that option "Oct" is selected in "DateTimePickerMonthSelect" select
    And I should see that option "2045" is selected in "DateTimePickerYearSelect" select
