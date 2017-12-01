@regression
@ticket-CRM-8143
@fixture-OroSalesBundle:OpportunityFixture.yml

Feature: Opportunity Navigation Items
  In order to manage lead feature
  as an Administrator
  I should be able to see or not see navigation items based on feature state

  Scenario: Pin and Add to favorites Opportunity Grid page
    Given I login as administrator
    And I should see Sales/Opportunities in main menu
    And I go to Sales/Opportunities
    And I should see Opportunity 1 in grid
    When I pin page
    And add page to favorites
    Then Opportunities link must be in pin holder
    And Favorites must contain "Opportunities - Sales"

  Scenario: Pin and Add to favorites Opportunity Grid page
    Given I click View Opportunity 1 in grid
    When I pin page
    And add page to favorites
    Then Opportunity 1 link must be in pin holder
    And Favorites must contain "Opportunity 1 - Opportunities - Sales"

  Scenario: Disable feature and validate links
    Given I go to Dashboards/Dashboard
    When I disable Opportunity feature
    And I reload the page
    Then Opportunities link must not be in pin holder
    And Favorites must not contain "Opportunities - Sales"
    And Opportunity 1 link must not be in pin holder
    And Favorites must not contain "Opportunity 1 - Opportunities - Sales"

  Scenario: Re-Enable feature and validate links
    Given I go to Dashboards/Dashboard
    When I enable Opportunity feature
    And I reload the page
    Then Opportunities link must be in pin holder
    And Favorites must contain "Opportunities - Sales"
    And Opportunity 1 link must be in pin holder
    And Favorites must contain "Opportunity 1 - Opportunities - Sales"
