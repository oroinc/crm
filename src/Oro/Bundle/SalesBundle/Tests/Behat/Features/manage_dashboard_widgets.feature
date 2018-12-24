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
    When I click "Widget Save Button"
    And I should see "Widget has been successfully configured" flash message
    Then I should see following grid:
      | Lead name     | Status       | Last contact datetime |
      | Alan          | Qualified    |                       |
      | John          | Disqualified |                       |
    When I click "Leads List Actions"
    And I click "Configure" in "Leads List" widget
    And I fill form with:
      | Excluded statuses | [New, Disqualified] |
      | Sort By           | First name          |
    And I click "Widget Save Button"
    And I should see "Widget has been successfully configured" flash message
    Then I should see following grid:
      | Lead name     | Status       | Last contact datetime |
      | Alan          | Qualified    |                       |

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
    And I should see "Widget has been successfully configured" flash message
    Then I should not see "Open Leads"
    And I should see "New Leads"

  Scenario: Add Opportunity statistics widget
    Given I click "Add widget"
    And I type "Opportunity statistics" in "Enter keyword"
    When I click "First Widget Add Button"
    And I click "Close" in modal window
    Then I should see "Opportunity Statistics" widget on dashboard

  Scenario: Check configuration of Opportunity Statistics widget
    Given I click "Opportunity Statistics Actions"
    And I click "Configure" in "Opportunity Statistics" widget
    When I click "Widget Save Button"
    And I should see "Widget has been successfully configured" flash message
    Then I should see "New Opportunities Count"
    And I should see "New Opportunities Amount"
    And I should see "Won Opportunities To Date Count"
    And I should see "Won Opportunities To Date Amount"
    When I click "Opportunity Statistics Actions"
    And I click "Configure" in "Opportunity Statistics" widget
    And I click "Delete column"
    And I click "Widget Save Button"
    And I should see "Widget has been successfully configured" flash message
    Then I should not see "New Opportunities Count"
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
    And I should see "Widget has been successfully configured" flash message
    Then I should see following grid:
      | Opportunity name | Budget amount | Budget amount ($) | Status     |
      | Opportunity 1    | $50.00        | $50.00            | Open       |
      | Opportunity 3    | $150.00       | $150.00           | Closed Won |
    When I click "Opportunities List Actions"
    And I click "Configure" in "Opportunities List" widget
    And I fill form with:
      | Excluded statuses | [Needs Analysis, Open] |
      | Sort By           | Opportunity name       |
    And I click "Widget Save Button"
    And I should see "Widget has been successfully configured" flash message
    Then I should see following grid:
      | Opportunity name | Budget amount | Budget amount ($) | Status     |
      | Opportunity 3    | $150.00       | $150.00           | Closed Won |
