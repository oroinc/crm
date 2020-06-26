@regression
@fixture-OroReportCRMBundle:AccountsByOpportunitiesReport.yml
@ticket-BAP-17648

Feature: Accounts by Opportunities Report
  In order to understand opportunities conditions by customers
  As an Administrator
  I want to have ability to build Accounts By Opportunities Report

  Scenario: Check Grid
    Given I login as administrator
    When I go to Reports & Segments/ Reports/ Accounts/ By Opportunities
    Then I should see following grid:
      | Account name | Open | Identification & Alignment | Needs Analysis | Solution Development | Negotiation | Closed Won | Closed Lost | Total |
      | Customer 1   | 0    | 0                          | 0              | 0                    | 0           | 2          | 0           | 2     |
    And should see "Grand Total 1 1 1 12 1 4 1 21"
    And number of records should be 20

  Scenario: Check Account Name Filter
    When I filter "Account name" as Contains "Customer 15"
    Then I should see following grid:
      | Account name |
      | Customer 15   |
    And records in grid should be 1
    And I reset "Account name" filter

  Scenario: Check Close Date filter
    Given records in grid should be 20
    When I filter Close date as between "today-3" and "today"
    Then I should see following grid:
      | Account name |
      | Customer 1   |
      | Customer 2   |
    And records in grid should be 2
    When I filter Close date as between "today-1" and "today"
    Then there are no records in grid
    And I reset "Close date" filter

  Scenario: Check Created At filter
    Given records in grid should be 20
    When I filter Created At as between "today-2" and "today-1"
    Then there are no records in grid
    When I filter Created At as between "today-1" and "today+1"
    Then records in grid should be 20
    And I reset "Created At" filter

  Scenario: Check Open Filter
    When I filter "Open" as equals "1"
    Then I should see following grid:
      | Account name |
      | Customer 5   |
    And records in grid should be 1
    And I reset "Open" filter

  Scenario: Check Closed Lost Filter
    When I filter "Closed Lost" as equals "1"
    Then I should see following grid:
      | Account name |
      | Customer 6   |
    And records in grid should be 1
    And I reset "Closed Lost" filter

  Scenario: Check Total Filter
    When I filter "Total" as equals "2"
    Then I should see following grid:
      | Account name |
      | Customer 1   |
    And records in grid should be 1
    And I reset "Total" filter

  Scenario: Check Closed Won Filter
    When I filter "Closed Won" as equals "2"
    Then I should see following grid:
      | Account name |
      | Customer 1   |
    And records in grid should be 1

  Scenario: Check Filter Applies After Different Actions
    Given I hide column Closed Won in grid
    Then I should see following grid:
      | Account name |
      | Customer 1   |
    And records in grid should be 1
    When I filter "Closed Won" as equals "0"
    And I should not see "Customer 3"
    And records in grid should be 17
    When I select 10 from per page list dropdown
    Then records in grid should be 10
    And I should not see "Customer 3"
    When I press next page button
    Then records in grid should be 7
    When I refresh "Accounts By Opportunities Grid" grid
    Then records in grid should be 7
    When I reload the page
    Then records in grid should be 7
    When I hide filter "Closed Won" in "Accounts By Opportunities Grid" grid
    Then there is 20 records in grid
    When I reset "Accounts By Opportunities Grid" grid
    Then there is 20 records in grid
    And records in grid should be 20

  Scenario: Sort by Account Name
    When I sort grid by "Account name"
    Then I should see following grid:
      | Account name |
      | Customer 1   |
      | Customer 10  |
    When I sort grid by "Account name" again
    Then I should see following grid:
      | Account name |
      | Customer 9   |
      | Customer 8   |
    And I reset "Accounts By Opportunities Grid" grid

  Scenario: Sort by Open
    When I sort grid by "Open"
    Then I should see following grid:
      | Open |
      | 0    |
      | 0    |
    When I sort grid by "Open" again
    Then I should see following grid:
      | Open |
      | 1    |
      | 0    |
    And I reset "Accounts By Opportunities Grid" grid

  Scenario: Sort by Identification & Alignment
    When I sort grid by "Identification & Alignment"
    Then I should see following grid:
      | Identification & Alignment |
      | 0                          |
      | 0                          |
    When I sort grid by "Identification & Alignment" again
    Then I should see following grid:
      | Identification & Alignment |
      | 1                          |
      | 0                          |
    And I reset "Accounts By Opportunities Grid" grid

  Scenario: Sort by Needs Analysis
    When I sort grid by "Needs Analysis"
    Then I should see following grid:
      | Needs Analysis |
      | 0              |
      | 0              |
    When I sort grid by "Needs Analysis" again
    Then I should see following grid:
      | Needs Analysis |
      | 1              |
      | 0              |
    And I reset "Accounts By Opportunities Grid" grid

  Scenario: Sort by Solution Development
    When I sort grid by "Solution Development"
    Then I should see following grid:
      | Solution Development |
      | 0                    |
      | 0                    |
    When I sort grid by "Solution Development" again
    Then I should see following grid:
      | Solution Development |
      | 1                    |
      | 1                    |
    And I reset "Accounts By Opportunities Grid" grid

  Scenario: Sort by Closed Won
    When I sort grid by "Closed Won"
    Then I should see following grid:
      | Closed Won |
      | 0          |
      | 0          |
    When I sort grid by "Closed Won" again
    Then I should see following grid:
      | Closed Won |
      | 2          |
      | 1          |
      | 1          |
      | 0          |
    And I reset "Accounts By Opportunities Grid" grid

  Scenario: Sort by Closed Lost
    When I sort grid by "Closed Lost"
    Then I should see following grid:
      | Closed Lost |
      | 0           |
      | 0           |
    When I sort grid by "Closed Lost" again
    Then I should see following grid:
      | Closed Lost |
      | 1           |
      | 0           |
    And I reset "Accounts By Opportunities Grid" grid

  Scenario: Sort by Total
    When I sort grid by "Total"
    Then I should see following grid:
      | Total |
      | 1     |
      | 1     |
    When I sort grid by "Total" again
    Then I should see following grid:
      | Total |
      | 2     |
      | 1     |
    And I reset "Accounts By Opportunities Grid" grid

  Scenario: Sort by Negotiation
    When I sort grid by "Negotiation"
    Then I should see following grid:
      | Negotiation |
      | 0           |
      | 0           |
    When I sort grid by "Negotiation" again
    Then I should see following grid:
      | Negotiation |
      | 1           |
      | 0           |

  Scenario: Check Sorter Applies After Different Actions
    Given I sort grid by "Account name"
    And sort grid by "Account name" again
    When I hide column Negotiation in grid
    Then I should see following grid:
      | Account name |
      | Customer 9   |
      | Customer 8   |
    When I select 10 from per page list dropdown
    Then records in grid should be 10
    And I should see following grid:
      | Account name |
      | Customer 9   |
      | Customer 8   |
    When I press next page button
    Then I should see following grid:
      | Account name |
      | Customer 18  |
      | Customer 17  |
    When I reload the page
    Then I should see following grid:
      | Account name |
      | Customer 18  |
      | Customer 17  |
    When I reset "Accounts By Opportunities Grid" grid
    Then there is 20 records in grid
    And records in grid should be 20

  Scenario: Check columns are loaded correctly
    Given I hide all columns in grid except Account Name
    When I show column Negotiation in grid
    Then I should see "Negotiation" column in grid
    And I should see following grid with exact columns order:
      | Account name | Negotiation |
      | Customer 1   | 0           |
      | Customer 2   | 0           |
    When I show column Closed Won in grid
    Then I should see "Closed Won" column in grid
    And I should see following grid with exact columns order:
      | Account name | Negotiation | Closed Won |
      | Customer 1   | 0           | 2          |
      | Customer 2   | 0           | 1          |

  Scenario: Check Columns Config Applies After Different Actions
    When I select 10 from per page list dropdown
    Then records in grid should be 10
    And I should see following grid with exact columns order:
      | Account name | Negotiation | Closed Won |
      | Customer 1   | 0           | 2          |
      | Customer 2   | 0           | 1          |
    When I press next page button
    And I should see following grid with exact columns order:
      | Account name | Negotiation | Closed Won |
      | Customer 11  | 0           | 0          |
      | Customer 12  | 0           | 0          |
    When I reload the page
    And I should see following grid with exact columns order:
      | Account name | Negotiation | Closed Won |
      | Customer 11  | 0           | 0          |
      | Customer 12  | 0           | 0          |
    When I reset "Accounts By Opportunities Grid" grid
    And I should see following grid:
      | Account name | Open | Identification & Alignment | Needs Analysis | Solution Development | Negotiation | Closed Won | Closed Lost | Total |
      | Customer 1   | 0    | 0                          | 0              | 0                    | 0           | 2          | 0           | 2     |
    And should see "Grand Total 1 1 1 12 1 4 1 21"
