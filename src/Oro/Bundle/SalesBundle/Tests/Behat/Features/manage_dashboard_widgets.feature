@ticket-CRM-9032
@ticket-CRM-9079
@ticket-BB-17275
@ticket-BAP-21315
@ticket-BAP-21448
@ticket-BAP-21510
@fixture-OroSalesBundle:leads_data.yml
@fixture-OroSalesBundle:opportunities_data.yml
Feature: Manage dashboard widgets
  In order to manage widgets on dashboard
  as an Administrator
  I should be able to add widget to dashboard and use different filters for widget content

  Scenario: Feature Background
    Given I login as administrator
    And I go to System/ Channels
    And I click "Create Channel"
    When I fill form with:
      | Name         | Business Channel |
      | Channel type | Sales            |
    And save and close form
    Then I should see "Channel saved" flash message

  Scenario: Add Leads List widget
    Given I am on dashboard
    And I click "Add widget"
    And I type "Leads List" in "Enter keyword"
    When I click "First Widget Add Button"
    And I click "Close" in modal window
    Then I should see "Leads List" widget on dashboard

  Scenario: Check configuration of Leads List widget
    Given I click "Leads List Actions"
    And I click "Configure" in "Leads List" widget
    And I fill form with:
      | Excluded statuses | New        |
      | Sort By           | First name |
    And I fill in "Leads List Sort" with "Descending"
    When I click "Widget Save Button"
    Then I should see "Widget has been successfully configured" flash message
    And I should see following grid:
      | Lead name     | Status       | Last contact datetime |
      | John          | Disqualified |                       |
      | Alan          | Qualified    |                       |
    When I click "Leads List Actions"
    And I click "Configure" in "Leads List" widget
    And I fill form with:
      | Excluded statuses | [New, Disqualified] |
      | Sort By           | First name          |
    And I click "Widget Save Button"
    Then I should see "Widget has been successfully configured" flash message
    And I should see following grid:
      | Lead name     | Status       | Last contact datetime |
      | Alan          | Qualified    |                       |
    When I click "Leads List Actions"
    And I click "Configure" in "Leads List" widget
    And I fill form with:
      | Excluded statuses | [Disqualified, Qualified] |
      | Sort By           | Last name |
    And I fill in "Leads List Sort" with "Ascending"
    When I click "Widget Save Button"
    Then I should see "Widget has been successfully configured" flash message
    And I should see following grid:
      | Lead name | Status | Last contact datetime |
      | Bruce     | New    |                       |

  Scenario: Add Lead Statistics widget
    Given I click "Add widget"
    And I type "Lead Statistics" in "Enter keyword"
    When I click "First Widget Add Button"
    And I click "Close" in modal window
    Then I should see "Lead Statistics" widget on dashboard

  Scenario: Check configuration of Lead Statistics widget
    Given I click "Leads Statistics Actions"
    And I click "Configure" in "Lead Statistics" widget
    When I click "Widget Save Button"
    And I should see "Widget has been successfully configured" flash message
    Then I should see "Open Leads"
    And I should see "New Leads"
    When I click "Leads Statistics Actions"
    And I click "Configure" in "Lead Statistics" widget
    And I click "Delete column"
    And I click "Widget Save Button"
    Then I should see "Widget has been successfully configured" flash message
    And I should not see "Open Leads"
    And I should see "New Leads"

  Scenario: Add Opportunity statistics widget
    Given I click "Add widget"
    And I type "Opportunity statistics" in "Enter keyword"
    When I click "First Widget Add Button"
    And I click "Close" in modal window
    Then I should see "Opportunity Statistics" widget on dashboard

  Scenario: Check availability additional option of custom date range
    Given I click "Opportunity Statistics Actions"
    When I click "Configure" in "Opportunity Statistics" widget
    And I should not see an "Date Custom Part" element
    And I fill "Opportunity Statistics Form" with:
      | Date range | Custom |
    Then I should see an "Date Custom Part" element

  Scenario: Check date range values with "between" type
    When I fill "Opportunity Statistics Form" with:
      | Start Date | Jun 7, 2022 |
      | End Date   | Jun 8, 2022 |
    And I click "Widget Save Button"
    Then I should see "Widget has been successfully configured" flash message
    And I should see "Date range: Jun 7, 2022 - Jun 8, 2022"

  Scenario: Check date range values with "earlier than" type
    When I click "Opportunity Statistics Actions"
    And I click "Configure" in "Opportunity Statistics" widget
    And I fill "Opportunity Statistics Form" with:
      | Type     | earlier than |
      | End Date | Jun 7, 2022  |
    And I click "Widget Save Button"
    Then I should see "Widget has been successfully configured" flash message
    And I should see "Date range: earlier than Jun 7, 2022"

  Scenario: Check date range values with "later than" type
    When I click "Opportunity Statistics Actions"
    And I click "Configure" in "Opportunity Statistics" widget
    And I fill "Opportunity Statistics Form" with:
      | Type       | later than  |
      | Start Date | Jun 7, 2022 |
    And I click "Widget Save Button"
    Then I should see "Widget has been successfully configured" flash message
    And I should see "Date range: later than Jun 7, 2022"

  Scenario: Check configuration of Opportunity Statistics widget
    Given I click "Opportunity Statistics Actions"
    And I click "Configure" in "Opportunity Statistics" widget
    When I click "Widget Save Button"
    Then I should see "Widget has been successfully configured" flash message
    And I should see "New Opportunities Count"
    And I should see "New Opportunities Amount"
    And I should see "Won Opportunities To Date Count"
    And I should see "Won Opportunities To Date Amount"
    When I click "Opportunity Statistics Actions"
    And I click "Configure" in "Opportunity Statistics" widget
    And I click "Delete column"
    And I click "Widget Save Button"
    Then I should see "Widget has been successfully configured" flash message
    And I should not see "New Opportunities Count"
    And I should see "New Opportunities Amount"
    And I should see "Won Opportunities To Date Count"
    And I should see "Won Opportunities To Date Amount"

  Scenario: Add Opportunities List widget
    Given I click "Add widget"
    And I type "Opportunities list" in "Enter keyword"
    When I click "First Widget Add Button"
    And I click "Close" in modal window
    Then I should see "Opportunities List" widget on dashboard

  Scenario: Check configuration of Opportunities List widget
    Given I click "Opportunities List Actions"
    And I click "Configure" in "Opportunities List" widget
    And I fill form with:
      | Excluded statuses | Needs Analysis   |
      | Sort By           | Opportunity name |
    When I click "Widget Save Button"
    Then I should see "Widget has been successfully configured" flash message
    And I should see following grid:
      | Opportunity name | Budget amount | Budget amount ($) | Status     |
      | Opportunity 1    | $50.00        | $50.00            | Open       |
      | Opportunity 3    | $150.00       | $150.00           | Closed Won |
    When I click "Opportunities List Actions"
    And I click "Configure" in "Opportunities List" widget
    And I fill form with:
      | Excluded statuses | [Needs Analysis, Open] |
      | Sort By           | Opportunity name       |
    And I click "Widget Save Button"
    Then I should see "Widget has been successfully configured" flash message
    And I should see following grid:
      | Opportunity name | Budget amount | Budget amount ($) | Status     |
      | Opportunity 3    | $150.00       | $150.00           | Closed Won |

  Scenario: Add Forecast widget
    When I click "Add widget"
    And I type "Forecast" in "Enter keyword"
    And I click "First Widget Add Button"
    And I click "Close" in modal window
    Then I should see "Forecast" widget on dashboard

  Scenario: Check date range values with "earlier than" type
    When I click "Forecast Actions"
    And I click "Configure" in "Forecast" widget
    And I fill "Forecast Form" with:
      | Date range | Custom       |
      | Type       | earlier than |
      | End Date   | Jun 8, 2022  |
    And I click "Widget Save Button"
    Then I should see "Widget has been successfully configured" flash message
    And I should see "Date range: earlier than Jun 8, 2022"

  Scenario: Check date range values with "between" type next day
    When I click "Opportunity Statistics Actions"
    And I click "Configure" in "Opportunity Statistics" widget
    And I fill "Opportunity Statistics Form" with:
      | Type       | between             |
      | Start Date | <Date:today +1 day> |
      | End Date   | <Date:today +1 day> |
    And I click "Widget Save Button"
    Then I should see "Widget has been successfully configured" flash message
    And I should see "−$300.00 (−100%)" in the "DeviationNegative" element
