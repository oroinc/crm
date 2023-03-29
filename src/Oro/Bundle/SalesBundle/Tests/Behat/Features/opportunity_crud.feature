@ticket-BAP-21510

Feature: Opportunity crud
  In order to have the ability to work with the opportunity
  As a Administrator
  I need to have the ability to create, view, update and delete the opportunity

  Scenario: Create Opportunity for Account
    Given I login as administrator
    When there is following Account:
      | name |
      | Acme |
    And I go to Customers/Accounts
    And click View Acme in grid
    And I follow "More actions"
    And click "Create Opportunity"
    And I fill "Opportunity Form" with:
      | Opportunity Name | Opportunity_1 |
      | Probability (%)  | 10            |
      | Budget Amount    | 50            |
      | Budget Currency  | $             |
    And save and close form
    Then I should see "Opportunity saved" flash message
    And I should see opportunity with:
      | Opportunity Name | Opportunity_1 |
      | Probability      | 10%           |
      | Budget Amount    | $50.00        |
      | Account          | Acme          |

  Scenario: Create Opportunity
    When I open Opportunity Create page
    And fill "Opportunity Form" with:
      | Opportunity name | Opportunity_2 |
      | Account          | Acme          |
      | Budget Amount    | 10000         |
      | Budget Currency  | $             |
      | Close Reason     | Cancelled     |
      | Probability (%)  | 50            |
      | Customer Need    | 10001         |
    And save and close form
    Then I should see "Opportunity saved" flash message
    And I should see opportunity with:
      | Opportunity Name | Opportunity_2 |
      | Account          | Acme          |
      | Probability      | 50%           |
      | Budget Amount    | $10,000.00    |
      | Close Reason     | Cancelled     |
      | Customer Need    | 10001         |

  Scenario: Opportunity Edit
    When I click "Edit Opportunity"
    And fill "Opportunity Form" with:
      | Opportunity name | Opportunity_3 |
    And save and close form
    Then I should see "Opportunity saved" flash message
    And I should see opportunity with:
      | Opportunity Name | Opportunity_3 |
      | Account          | Acme          |
      | Probability      | 50%           |
      | Budget Amount    | $10,000.00    |
      | Close Reason     | Cancelled     |
      | Customer Need    | 10001         |

  Scenario: Opportunity View in grid
    When go to Sales/ Opportunities
    Then I should see following grid containing rows:
      | Opportunity Name | Budget Amount | Probability | Status |
      | Opportunity_3    | $10,000.00    | 50%         | Open   |
      | Opportunity_1    | $50.00        | 10%         | Open   |
    And I filter Budget Amount as equals "1,500.00"
    And there is no records in grid
    And I reset Budget Amount filter
    And I filter Opportunity Name as is equal to "Opportunity_3"
    And I should see following grid:
      | Opportunity Name | Budget Amount | Probability | Status |
      | Opportunity_3    | $10,000.00    | 50%         | Open   |

  Scenario: Opportunity Delete
    When I click Delete Opportunity_3 in grid
    And I confirm deletion
    Then I should see "Item deleted" flash message
    And there is no records in grid

  Scenario: Check Opportunities by Status widget
    When I login as administrator
    Then I should see "Opportunities by Status" widget on dashboard
