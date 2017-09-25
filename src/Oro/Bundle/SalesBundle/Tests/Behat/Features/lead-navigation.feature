@ticket-CRM-8143
@fixture-OroSalesBundle:LeadFixture.yml
# @TODO check pins separately (e.g. in opportunity-navigation.feature)
Feature: Lead Navigation Items
  In order to manage lead feature
  as an Administrator
  I should be able to see or not see navigation items based on feature state

  Scenario: Pin and Add to favorites Lead Grid page
    Given I login as administrator
    And I should see Sales/Leads in main menu
    And I go to Sales/Leads
    And I should see Lead 1 in grid
    When I pin page
    And add page to favorites
    Then Leads link must be in pin holder
    And Favorites must contain "Leads - Sales"

  Scenario: Pin and Add to favorites Lead View page
    Given I click View Lead 1 in grid
    When I pin page
    And add page to favorites
    Then Lead 1 link must be in pin holder
    And Favorites must contain "Lead 1 - Leads - Sales"

  Scenario: Disable feature and validate links
    Given I go to Dashboards/Dashboard
    When I disable Lead feature
    And I reload the page
    Then Leads link must not be in pin holder
    And Favorites must not contain "Leads - Sales"
    And Lead 1 link must not be in pin holder
    And Favorites must not contain "Lead 1 - Leads - Sales"

  Scenario: Re-Enable feature and validate links
    Given I go to Dashboards/Dashboard
    When I enable Lead feature
    And I reload the page
    Then Leads link must be in pin holder
    And Favorites must contain "Leads - Sales"
    And Lead 1 link must be in pin holder
    And Favorites must contain "Lead 1 - Leads - Sales"
