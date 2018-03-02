@ticket-BAP-12348

Feature: Leads by date report
  In order to understand leads count in specific periods of time
  As an Administrator
  I want to have ability to build Leads By Date Report

  Scenario: Check report
    Given leads by date report fixture loaded
    And I login as administrator
    And I go to Reports & Segments/ Reports/ Leads/ Leads By Date
    When I filter Created Date as between "Jan 1, 2016 11:30 AM" and "Jan 3, 2016 11:30 AM"
    Then number of records should be 3
    And I should see following grid:
      | Created Date | Leads Count |
      | Jan 1, 2016  | 4           |
      | Jan 2, 2016  | 1           |
      | Jan 3, 2016  | 3           |
