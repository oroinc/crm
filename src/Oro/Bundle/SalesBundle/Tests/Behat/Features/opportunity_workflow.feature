Feature: Opportunity workflow
  In order to have roadmap for opportunity flow
  As sales rep
  I need opportunity workflow

  Scenario: New Opportunity
    Given I login as administrator
    And there is following Account:
      | name |
      | Acme |
    When I open Opportunity Create page
    And fill form with:
      | Opportunity Name | Summer sales |
      | Account          | Acme         |
    And save and close form
    Then I should see "Opportunity saved" flash message

  Scenario: Enable opportuniy workflow
    Given I go to Sales/ Opportunities
    And I click on Summer sales in grid
    And I should not see "Start Opportunity Management Flow"
    When I go to System/ Workflows
    And click on Opportunity Management Flow in grid
    And click "Activate"
    And click "Activate"
    And I go to Sales/ Opportunities
    And I click on Summer sales in grid
    Then I should see "Start Opportunity Management Flow"

  Scenario: Defelop opportunity
    Given I click "Start Opportunity Management Flow"
    And I click "Submit"
    When I click "Develop"
    And I fill "Develop Opportunity Form" with:
      | Budget Amount     | 1000              |
      | Customer need     | Need to be pretty |
      | Proposed solution | Make it sharp     |
    And press "Submit"
    Then I should see opportunity with:
      | Budget Amount     | $1,000.00         |
      | Status            | Open              |
      | Probability       | 0%                |
      | Customer need     | Need to be pretty |
      | Proposed solution | Make it sharp     |

  Scenario: Close as won
    Given I click "Close as Won"
    When I fill "Close As Won Opportunity Form" with:
      | Close Revenue       | 5000        |
    And I click "Submit"
    Then I should see opportunity with:
      | Budget Amount | $1,000.00  |
      | Close Revenue | $5,000.00  |
      | Probability   | 100%       |
      | Status        | Closed Won |

  Scenario: Reopen opportunity
    Given I press "Reopen"
    When I fill "Develop Opportunity Form" with:
      | Budget Amount     | 5000               |
      | Status            | Needs Analysis     |
      | Customer need     | We need to rethink |
      | Proposed solution | Have some beer     |
    And press "Submit"
    Then I should see opportunity with:
      | Budget Amount     | $5,000.00          |
      | Status            | Needs Analysis     |
      | Probability       | 0%                 |
      | Customer need     | We need to rethink |
      | Proposed solution | Have some beer     |

  Scenario: Close opportunity as lost
    Given I press "Close as Lost"
    When I fill "Close As Lost Opportunity Form" with:
      | Close reason      | Cancelled          |
    And I click "Submit"
    Then I should see opportunity with:
      | Budget Amount | $5,000.00   |
      | Close Reason  | Cancelled   |
      | Close Revenue | $0.00       |
      | Probability   | 0%          |
      | Status        | Closed Lost |
