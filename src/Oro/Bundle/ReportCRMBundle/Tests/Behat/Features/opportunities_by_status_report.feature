@regression
@fixture-OroReportCRMBundle:AccountsByOpportunitiesReport.yml
@ticket-CRM-8746
@ticket-BAP-17648

Feature: Opportunities by Status Report
  In order to understand opportunities count in specific status
  As an Administrator
  I want to have ability to build Opportunity By Status Report

  Scenario: Check Grid
    Given I login as administrator
    When I go to Reports & Segments/ Reports/ Opportunities/ Opportunities By Status
    Then number of records should be 7
    And I should see following grid:
      | Status                     | Number of opportunities | Close Revenue | Budget Amount |
      | Open                       | 1                       | $0.00         | $5.00         |
      | Identification & Alignment | 1                       | $0.00         | $4.00         |
      | Needs Analysis             | 1                       | $0.00         | $7.00         |
      | Solution Development       | 12                      | $0.00         | $174.00       |
      | Negotiation                | 1                       | $0.00         | $8.00         |
      | Closed Won                 | 4                       | $1,150.00     | $27.00        |
      | Closed Lost                | 1                       | $256.00       | $6.00         |
    And should see "Grand Total 21 $1,406.00 $231.00"

  Scenario: Check Status Filter
    Given number of records should be 7
    When I choose filter for Status as Is Any Of "Closed Lost"
    Then number of records should be 1
    And I should see following grid:
      | Status      | Number of opportunities | Close Revenue | Budget Amount |
      | Closed Lost | 1                       | $256.00       | $6.00         |
    And should see "Grand Total 1 $256.00 $6.00"
    And records in grid should be 1
    And I reset Status filter

  Scenario: Check Number of Opportunities Filter
    Given records in grid should be 7
    When I filter Number of opportunities as equals "4"
    And I should see following grid:
      | Status     | Number of opportunities | Close Revenue | Budget Amount |
      | Closed Won | 4                       | $1,150.00     | $27.00        |
    And should see "Grand Total 4 $1,150.00 $27.00"
    And records in grid should be 1
    And I reset "Number of opportunities" filter

  Scenario: Check Close Revenue Filter
    Given records in grid should be 7
    When I filter Close Revenue as equals "256"
    And I should see following grid:
      | Status      | Number of opportunities | Close Revenue | Budget Amount |
      | Closed Lost | 1                       | $256.00       | $6.00         |
    And should see "Grand Total 1 $256.00 $6.00"
    And records in grid should be 1
    And I reset "Close Revenue" filter

  Scenario: Check Expected Close Date filter
    Given records in grid should be 7
    When I filter Expected close date as between "today - 3" and "today"
    Then number of records should be 7
    And I should see following grid:
      | Status                     | Number of opportunities | Close Revenue | Budget Amount |
      | Open                       | 0                       | $0.00         | $0.00         |
      | Identification & Alignment | 0                       | $0.00         | $0.00         |
      | Needs Analysis             | 0                       | $0.00         | $0.00         |
      | Solution Development       | 0                       | $0.00         | $0.00         |
      | Negotiation                | 0                       | $0.00         | $0.00         |
      | Closed Won                 | 3                       | $150.00       | $24.00        |
      | Closed Lost                | 0                       | $0.00         | $0.00         |
    And should see "Grand Total 3 $150.00 $24.00"
    And records in grid should be 7
    When I filter Expected close date as between "today - 1" and "today"
    And I should see following grid:
      | Status                     | Number of opportunities | Close Revenue | Budget Amount |
      | Open                       | 0                       | $0.00         | $0.00         |
      | Identification & Alignment | 0                       | $0.00         | $0.00         |
      | Needs Analysis             | 0                       | $0.00         | $0.00         |
      | Solution Development       | 0                       | $0.00         | $0.00         |
      | Negotiation                | 0                       | $0.00         | $0.00         |
      | Closed Won                 | 0                       | $0.00         | $0.00         |
      | Closed Lost                | 0                       | $0.00         | $0.00         |
    And records in grid should be 7
    And I reset "Expected close date" filter

  Scenario: Check Created At filter
    Given records in grid should be 7
    When I filter Created At as between "now + 1" and "now + 2"
    Then number of records should be 7
    And I should see following grid:
      | Status                     | Number of opportunities | Close Revenue | Budget Amount |
      | Open                       | 0                       | $0.00         | $0.00         |
      | Identification & Alignment | 0                       | $0.00         | $0.00         |
      | Needs Analysis             | 0                       | $0.00         | $0.00         |
      | Solution Development       | 0                       | $0.00         | $0.00         |
      | Negotiation                | 0                       | $0.00         | $0.00         |
      | Closed Won                 | 0                       | $0.00         | $0.00         |
      | Closed Lost                | 0                       | $0.00         | $0.00         |
    And records in grid should be 7
    When I filter Created At as between "now - 1" and "now + 1"
    And I should see following grid:
      | Status                     | Number of opportunities | Close Revenue | Budget Amount |
      | Open                       | 1                       | $0.00         | $5.00         |
      | Identification & Alignment | 1                       | $0.00         | $4.00         |
      | Needs Analysis             | 1                       | $0.00         | $7.00         |
      | Solution Development       | 12                      | $0.00         | $174.00       |
      | Negotiation                | 1                       | $0.00         | $8.00         |
      | Closed Won                 | 4                       | $1,150.00     | $27.00        |
      | Closed Lost                | 1                       | $256.00       | $6.00         |
    And should see "Grand Total 21 $1,406.00 $231.00"
    And records in grid should be 7
    And I reset "Created At" filter

  Scenario: Check Updated At filter
    Given records in grid should be 7
    When I filter Updated At as between "now + 1" and "now + 2"
    Then number of records should be 7
    And I should see following grid:
      | Status                     | Number of opportunities | Close Revenue | Budget Amount |
      | Open                       | 0                       | $0.00         | $0.00         |
      | Identification & Alignment | 0                       | $0.00         | $0.00         |
      | Needs Analysis             | 0                       | $0.00         | $0.00         |
      | Solution Development       | 0                       | $0.00         | $0.00         |
      | Negotiation                | 0                       | $0.00         | $0.00         |
      | Closed Won                 | 0                       | $0.00         | $0.00         |
      | Closed Lost                | 0                       | $0.00         | $0.00         |
    And records in grid should be 7
    When I filter Updated At as between "now - 1" and "now + 1"
    And I should see following grid:
      | Status                     | Number of opportunities | Close Revenue | Budget Amount |
      | Open                       | 1                       | $0.00         | $5.00         |
      | Identification & Alignment | 1                       | $0.00         | $4.00         |
      | Needs Analysis             | 1                       | $0.00         | $7.00         |
      | Solution Development       | 12                      | $0.00         | $174.00       |
      | Negotiation                | 1                       | $0.00         | $8.00         |
      | Closed Won                 | 4                       | $1,150.00     | $27.00        |
      | Closed Lost                | 1                       | $256.00       | $6.00         |
    And should see "Grand Total 21 $1,406.00 $231.00"
    And records in grid should be 7
    And I reset "Updated At" filter

  Scenario: Check Budget Amount Filter
    Given records in grid should be 7
    When I filter Budget Amount as equals "174"
    And I should see following grid:
      | Status               | Number of opportunities | Close Revenue | Budget Amount |
      | Solution Development | 12                      | $0.00         | $174.00       |
    And should see "Grand Total 12 $0.00 $174.00"
    And records in grid should be 1

  Scenario: Check Filter Applies After Different Actions
    Given I hide column Budget Amount in grid
    And I should see following grid:
      | Status               | Number of opportunities | Close Revenue |
      | Solution Development | 12                      | $0.00         |
    And records in grid should be 1
    When I select 10 from per page list dropdown
    Then records in grid should be 1
    When I refresh "Opportunities By Status Grid" grid
    Then I should see following grid:
      | Status               | Number of opportunities | Close Revenue |
      | Solution Development | 12                      | $0.00         |
    And records in grid should be 1
    When I reload the page
    Then records in grid should be 1
    When I hide filter "Budget Amount" in "Opportunities By Status Grid" grid
    Then there is 7 records in grid
    When I reset "Opportunities By Status Grid" grid
    Then I should see following grid:
      | Status                     | Number of opportunities | Close Revenue | Budget Amount |
      | Open                       | 1                       | $0.00         | $5.00         |
      | Identification & Alignment | 1                       | $0.00         | $4.00         |
      | Needs Analysis             | 1                       | $0.00         | $7.00         |
      | Solution Development       | 12                      | $0.00         | $174.00       |
      | Negotiation                | 1                       | $0.00         | $8.00         |
      | Closed Won                 | 4                       | $1,150.00     | $27.00        |
      | Closed Lost                | 1                       | $256.00       | $6.00         |
    And should see "Grand Total 21 $1,406.00 $231.00"
    And there is 7 records in grid
    And records in grid should be 7

  Scenario: Sort by Status
    When I sort grid by "Status"
    Then I should see following grid:
      | Status                     | Number of opportunities | Close Revenue | Budget Amount |
      | Closed Lost                | 1                       | $256.00       | $6.00         |
      | Closed Won                 | 4                       | $1,150.00     | $27.00        |
      | Identification & Alignment | 1                       | $0.00         | $4.00         |
      | Needs Analysis             | 1                       | $0.00         | $7.00         |
      | Negotiation                | 1                       | $0.00         | $8.00         |
      | Open                       | 1                       | $0.00         | $5.00         |
      | Solution Development       | 12                      | $0.00         | $174.00       |
    When I sort grid by "Status" again
    Then I should see following grid:
      | Status                     | Number of opportunities | Close Revenue | Budget Amount |
      | Solution Development       | 12                      | $0.00         | $174.00       |
      | Open                       | 1                       | $0.00         | $5.00         |
      | Negotiation                | 1                       | $0.00         | $8.00         |
      | Needs Analysis             | 1                       | $0.00         | $7.00         |
      | Identification & Alignment | 1                       | $0.00         | $4.00         |
      | Closed Won                 | 4                       | $1,150.00     | $27.00        |
      | Closed Lost                | 1                       | $256.00       | $6.00         |
    And I reset "Opportunities By Status Grid" grid

  Scenario: Sort by Number of opportunities
    When I sort grid by "Number of opportunities"
    Then I should see following grid:
      | Number of opportunities |
      | 1                       |
      | 1                       |
      | 1                       |
      | 1                       |
      | 1                       |
      | 4                       |
      | 12                      |
    When I sort grid by "Number of opportunities" again
    Then I should see following grid:
      | Number of opportunities |
      | 12                      |
      | 4                       |
      | 1                       |
      | 1                       |
      | 1                       |
      | 1                       |
      | 1                       |
    And I reset "Opportunities By Status Grid" grid

  Scenario: Sort by Close Revenue
    When I sort grid by "Close Revenue"
    Then I should see following grid:
      | Close Revenue |
      | $0.00         |
      | $0.00         |
      | $0.00         |
      | $0.00         |
      | $0.00         |
      | $256.00       |
      | $1,150.00     |
    When I sort grid by "Close Revenue" again
    Then I should see following grid:
      | Close Revenue |
      | $1,150.00     |
      | $256.00       |
      | $0.00         |
      | $0.00         |
      | $0.00         |
      | $0.00         |
      | $0.00         |
    And I reset "Opportunities By Status Grid" grid

  Scenario: Sort by Budget Amount
    When I sort grid by "Budget Amount"
    Then I should see following grid:
      | Status                     | Number of opportunities | Close Revenue | Budget Amount |
      | Identification & Alignment | 1                       | $0.00         | $4.00         |
      | Open                       | 1                       | $0.00         | $5.00         |
      | Closed Lost                | 1                       | $256.00       | $6.00         |
      | Needs Analysis             | 1                       | $0.00         | $7.00         |
      | Negotiation                | 1                       | $0.00         | $8.00         |
      | Closed Won                 | 4                       | $1,150.00     | $27.00        |
      | Solution Development       | 12                      | $0.00         | $174.00       |
    When I sort grid by "Budget Amount" again
    Then I should see following grid:
      | Status                     | Number of opportunities | Close Revenue | Budget Amount |
      | Solution Development       | 12                      | $0.00         | $174.00       |
      | Closed Won                 | 4                       | $1,150.00     | $27.00        |
      | Negotiation                | 1                       | $0.00         | $8.00         |
      | Needs Analysis             | 1                       | $0.00         | $7.00         |
      | Closed Lost                | 1                       | $256.00       | $6.00         |
      | Open                       | 1                       | $0.00         | $5.00         |
      | Identification & Alignment | 1                       | $0.00         | $4.00         |

  Scenario: Check Sorter Applies After Different Actions
    Given I hide column Budget Amount in grid
    Then I should see following grid:
      | Status                     | Number of opportunities | Close Revenue |
      | Solution Development       | 12                      | $0.00         |
      | Closed Won                 | 4                       | $1,150.00     |
      | Negotiation                | 1                       | $0.00         |
      | Needs Analysis             | 1                       | $0.00         |
      | Closed Lost                | 1                       | $256.00       |
      | Open                       | 1                       | $0.00         |
      | Identification & Alignment | 1                       | $0.00         |
    When I select 10 from per page list dropdown
    Then I should see following grid:
      | Status                     | Number of opportunities | Close Revenue |
      | Solution Development       | 12                      | $0.00         |
      | Closed Won                 | 4                       | $1,150.00     |
      | Negotiation                | 1                       | $0.00         |
      | Needs Analysis             | 1                       | $0.00         |
      | Closed Lost                | 1                       | $256.00       |
      | Open                       | 1                       | $0.00         |
      | Identification & Alignment | 1                       | $0.00         |
    And records in grid should be 7
    When I reload the page
    Then I should see following grid:
      | Status                     | Number of opportunities | Close Revenue |
      | Solution Development       | 12                      | $0.00         |
      | Closed Won                 | 4                       | $1,150.00     |
      | Negotiation                | 1                       | $0.00         |
      | Needs Analysis             | 1                       | $0.00         |
      | Closed Lost                | 1                       | $256.00       |
      | Open                       | 1                       | $0.00         |
      | Identification & Alignment | 1                       | $0.00         |
    And records in grid should be 7
    When I reset "Opportunities By Status Grid" grid
    Then I should see following grid:
      | Status                     | Number of opportunities | Close Revenue | Budget Amount |
      | Open                       | 1                       | $0.00         | $5.00         |
      | Identification & Alignment | 1                       | $0.00         | $4.00         |
      | Needs Analysis             | 1                       | $0.00         | $7.00         |
      | Solution Development       | 12                      | $0.00         | $174.00       |
      | Negotiation                | 1                       | $0.00         | $8.00         |
      | Closed Won                 | 4                       | $1,150.00     | $27.00        |
      | Closed Lost                | 1                       | $256.00       | $6.00         |
    And there is 7 records in grid
    And records in grid should be 7

  Scenario: Check columns are loaded correctly
    Given I hide all columns in grid except Status
    When I show column Number of opportunities in grid
    Then I should see "Number of opportunities" column in grid
    And I should see following grid with exact columns order:
      | Status                     | Number of opportunities |
      | Open                       | 1                       |
      | Identification & Alignment | 1                       |
      | Needs Analysis             | 1                       |
      | Solution Development       | 12                      |
      | Negotiation                | 1                       |
      | Closed Won                 | 4                       |
      | Closed Lost                | 1                       |
    When I show column Budget Amount in grid
    Then I should see "Budget Amount" column in grid
    And I should see following grid with exact columns order:
      | Status                     | Number of opportunities | Budget Amount |
      | Open                       | 1                       | $5.00         |
      | Identification & Alignment | 1                       | $4.00         |
      | Needs Analysis             | 1                       | $7.00         |
      | Solution Development       | 12                      | $174.00       |
      | Negotiation                | 1                       | $8.00         |
      | Closed Won                 | 4                       | $27.00        |
      | Closed Lost                | 1                       | $6.00         |

  Scenario: Check Columns Config Applies After Different Actions
    Given records in grid should be 7
    When I select 10 from per page list dropdown
    Then records in grid should be 7
    And I should see following grid with exact columns order:
      | Status                     | Number of opportunities | Budget Amount |
      | Open                       | 1                       | $5.00         |
      | Identification & Alignment | 1                       | $4.00         |
      | Needs Analysis             | 1                       | $7.00         |
      | Solution Development       | 12                      | $174.00       |
      | Negotiation                | 1                       | $8.00         |
      | Closed Won                 | 4                       | $27.00        |
      | Closed Lost                | 1                       | $6.00         |
    When I reload the page
    And I should see following grid with exact columns order:
      | Status                     | Number of opportunities | Budget Amount |
      | Open                       | 1                       | $5.00         |
      | Identification & Alignment | 1                       | $4.00         |
      | Needs Analysis             | 1                       | $7.00         |
      | Solution Development       | 12                      | $174.00       |
      | Negotiation                | 1                       | $8.00         |
      | Closed Won                 | 4                       | $27.00        |
      | Closed Lost                | 1                       | $6.00         |
    When I reset "Opportunities By Status Grid" grid
    Then I should see following grid with exact columns order:
      | Status                     | Number of opportunities | Close Revenue | Budget Amount |
      | Open                       | 1                       | $0.00         | $5.00         |
      | Identification & Alignment | 1                       | $0.00         | $4.00         |
      | Needs Analysis             | 1                       | $0.00         | $7.00         |
      | Solution Development       | 12                      | $0.00         | $174.00       |
      | Negotiation                | 1                       | $0.00         | $8.00         |
      | Closed Won                 | 4                       | $1,150.00     | $27.00        |
      | Closed Lost                | 1                       | $256.00       | $6.00         |
