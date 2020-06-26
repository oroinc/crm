@regression
@ticket-BAP-12348
@ticket-BAP-17648

Feature: Leads by Date Report
  In order to understand leads count in specific periods of time
  As an Administrator
  I want to have ability to build Leads By Date Report

  Scenario: Check Grid
    Given leads by date report fixture loaded
    And I login as administrator
    When I go to Reports & Segments/ Reports/ Leads/ Leads By Date
    Then number of records should be 7
    And I should see following grid:
      | Created Date | Leads Count |
      | Jan 1, 2016  | 4           |
      | Jan 2, 2016  | 1           |
      | Jan 3, 2016  | 3           |
      | Feb 1, 2016  | 2           |
      | May 1, 2016  | 2           |
      | Jun 1, 2016  | 2           |
      | Jan 1, 2017  | 2           |
    And should see "Grand Total 16"

  Scenario: Check Created At Filter
    Given records in grid should be 7
    When I filter Created Date as between "Jan 1, 2016 11:30 AM" and "Jan 3, 2016 11:30 AM"
    Then number of records should be 3
    And I should see following grid:
      | Created Date | Leads Count |
      | Jan 1, 2016  | 4           |
      | Jan 2, 2016  | 1           |
      | Jan 3, 2016  | 3           |
    And I reset "Created At" filter

  Scenario: Check Leads Count Filter
    Given records in grid should be 7
    When I filter Leads Count as equals "4"
    Then number of records should be 1
    And I should see following grid:
      | Created Date | Leads Count |
      | Jan 1, 2016  | 4           |
    And should see "Grand Total 4"
    And I reset "Leads Count" filter
    When I filter Leads Count as equals "1"
    Then number of records should be 1
    And I should see following grid:
      | Created Date | Leads Count |
      | Jan 2, 2016  | 1           |
    And should see "Grand Total 1"

  Scenario: Check Filter Applies After Different Actions
    Given I hide column Leads count in grid
    And I should see following grid:
      | Created Date |
      | Jan 2, 2016  |
    And records in grid should be 1
    When I filter "Leads Count" as equals "2"
    Then I should not see "Jan 1, 2016"
    And records in grid should be 4
    When I refresh "Leads By Date Grid" grid
    Then records in grid should be 4
    When I reload the page
    Then records in grid should be 4
    When I hide filter "Leads count" in "Leads By Date Grid" grid
    Then there is 7 records in grid
    When I reset "Leads By Date Grid" grid
    Then there is 7 records in grid
    And records in grid should be 7

  Scenario: Sort by Created Date
    Given I should see following grid:
      | Created Date | Leads Count |
      | Jan 1, 2016  | 4           |
      | Jan 2, 2016  | 1           |
      | Jan 3, 2016  | 3           |
    When I sort grid by "Created date"
    Then I should see following grid:
      | Created Date | Leads Count |
      | Jan 1, 2016  | 4           |
      | Jan 2, 2016  | 1           |
      | Jan 3, 2016  | 3           |
    When I sort grid by "Created date" again
    Then I should see following grid:
      | Created Date | Leads Count |
      | Jan 1, 2017  | 2           |
      | Jun 1, 2016  | 2           |
      | May 1, 2016  | 2           |
    And I reset "Leads By Date Grid" grid

  Scenario: Sort by Leads Count
    Given I should see following grid:
      | Created Date | Leads Count |
      | Jan 1, 2016  | 4           |
      | Jan 2, 2016  | 1           |
      | Jan 3, 2016  | 3           |
    When I sort grid by "Leads count"
    Then I should see following grid:
      | Leads Count |
      | 1           |
      | 2           |
      | 2           |
    When I sort grid by "Leads count" again
    Then I should see following grid:
      | Created Date | Leads Count |
      | Jan 1, 2016  | 4           |
      | Jan 3, 2016  | 3           |
      | Feb 1, 2016  | 2           |

  Scenario: Check Sorter Applies After Different Actions
    Given I hide column Leads count in grid
    Then I should see following grid:
      | Created Date |
      | Jan 1, 2016  |
      | Jan 3, 2016  |
      | Feb 1, 2016  |
    When I refresh "Leads By Date Grid" grid
    Then I should see following grid:
      | Created Date |
      | Jan 1, 2016  |
      | Jan 3, 2016  |
      | Feb 1, 2016  |
    When I reload the page
    Then I should see following grid:
      | Created Date |
      | Jan 1, 2016  |
      | Jan 3, 2016  |
      | Feb 1, 2016  |
    When I reset "Leads By Date Grid" grid
    Then I should see following grid:
      | Created Date | Leads Count |
      | Jan 1, 2016  | 4           |
      | Jan 2, 2016  | 1           |
      | Jan 3, 2016  | 3           |
