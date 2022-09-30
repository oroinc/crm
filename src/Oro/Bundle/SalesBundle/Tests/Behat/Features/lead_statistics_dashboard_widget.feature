@ticket-BAP-21510
@fixture-OroSalesBundle:leads_data.yml
Feature: Lead statistics dashboard widget
  In order to manage lead statistics widgets on the dashboard
  as an Administrator
  I should be able to add a lead statistics widget to the dashboard and use different filters for widget content

  Scenario: Feature Background
    Given I login as administrator
    And I go to System/ Channels
    And I click "Create Channel"
    When I fill form with:
      | Name         | Business Channel |
      | Channel type | Sales            |
    And save and close form
    Then I should see "Channel saved" flash message

  Scenario: Add Lead Statistics widget
    Given I am on dashboard
    When I click "Add widget"
    And I type "Lead Statistics" in "Enter keyword"
    And I click "First Widget Add Button"
    And I click "Close" in modal window
    Then I should see "Lead Statistics" widget on dashboard

  Scenario: Apply "All time" date range filter
    When I click "Leads Statistics Actions"
    And I click "Configure" in "Lead Statistics" widget
    And I fill form with:
      | Date range | All time |
    And I click "Widget Save Button"
    Then I should see "Widget has been successfully configured" flash message
    And I should see "Open Leads"
    And I should see "New Leads"

  Scenario: Apply "Today" date range filter
    When I click "Leads Statistics Actions"
    And I click "Configure" in "Lead Statistics" widget
    And I fill form with:
      | Date range | Today |
    And I click "Widget Save Button"
    Then I should see "Widget has been successfully configured" flash message
    And I should see "Open Leads"
    And I should see "New Leads"
    And I should see "+3" in the "Lead Statistics Widget New Leads count" element

  Scenario: Apply "Custom" date range filter
    When I click "Leads Statistics Actions"
    And I click "Configure" in "Lead Statistics" widget
    And fill "Lead Statistics Form" with:
      | Date range       | Custom            |
      | Start Date range | <Date:2017-01-01> |
      | End Date range   | <Date:today+1day> |
    And I click "Widget Save Button"
    Then I should see "Widget has been successfully configured" flash message
    And I should see "Open Leads"
    And I should see "New Leads"
    And I should see "+3" in the "Lead Statistics Widget New Leads count" element

  Scenario: Apply advanced filters without owner filter
    When I click "Leads Statistics Actions"
    And I click "Configure" in "Lead Statistics" widget
    And I fill form with:
      | Date range | Today |
    And I click "Expand"
    And add the following filters:
      | Field Condition | Created At |
    And fill "Lead Statistics Form" with:
      | Start Date filter | <Date:today-1day> |
      | End Date filter   | <Date:today+1day> |
    And I click "Widget Save Button"
    Then I should see "Widget has been successfully configured" flash message
    And I should see "Open Leads"
    And I should see "New Leads"
    And I should see "+3" in the "Lead Statistics Widget New Leads count" element

  Scenario: Apply owner filter with date range filter on today
    When I click "Leads Statistics Actions"
    Then I click "Delete" in "Lead Statistics" widget
    And I confirm deletion

    When I click "Add widget"
    And I type "Lead Statistics" in "Enter keyword"
    And I click "First Widget Add Button"
    And I click "Close" in modal window
    Then I should see "Lead Statistics" widget on dashboard

    When I click "Leads Statistics Actions"
    And I click "Configure" in "Lead Statistics" widget
    And I fill form with:
      | Date range | Today        |
      | Owner      | Current User |
    And I click "Expand"
    And add the following filters:
      | Field Condition | Updated At | later than |
    And fill "Lead Statistics Form" with:
      | Start Date filter | Jan 1, 2016 |
    And I click "Delete column"
    And I click "Delete column"
    And I click "Widget Save Button"
    Then I should see "Widget has been successfully configured" flash message
    And I should not see "Open Leads"
    And I should not see "New Leads"
    And I should see "No available metrics"

  Scenario: Apply advanced filters with owner filter
    When I click "Leads Statistics Actions"
    Then I click "Delete" in "Lead Statistics" widget
    And I confirm deletion

    When I click "Add widget"
    And I type "Lead Statistics" in "Enter keyword"
    And I click "First Widget Add Button"
    And I click "Close" in modal window
    Then I should see "Lead Statistics" widget on dashboard

    When I click "Leads Statistics Actions"
    And I click "Configure" in "Lead Statistics" widget
    And I fill form with:
      | Date range | Today        |
      | Owner      | Current User |
    And I click "Previous period disable"
    And I click "Expand"
    And add the following filters:
      | Field Condition | Updated At |
    And fill "Lead Statistics Form" with:
      | Start Date filter | <Date:today-1day> |
      | End Date filter | <Date:today+1day> |
    And I click "Delete column"
    And I click "Delete column"
    And I click "Widget Save Button"
    Then I should see "Widget has been successfully configured" flash message
    And I should not see "Open Leads"
    And I should not see "New Leads"
