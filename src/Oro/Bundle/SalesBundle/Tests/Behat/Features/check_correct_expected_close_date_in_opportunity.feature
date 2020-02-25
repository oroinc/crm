@ticket-BAP-19218
@fixture-OroSalesBundle:opportunities_data.yml
Feature: Check correct expected close date in opportunity
  In order to edit opportunity and add expected close date and this date will be show in grid correctly when we will change timezone
  As a user
  I need to see correct expected close date in opportunity when change any timezone

  Scenario: Configure default time zone
    Given I login as administrator
    And I go to System / Configuration
    And I follow "System Configuration/General Setup/Localization" on configuration sidebar
    When uncheck "Use default" for "Timezone" field
    And I fill form with:
      | Timezone | America/Nome |
    And I save form
    Then I should see "Configuration saved" flash message

  Scenario: Edit opportunity and check correct expected close date in grid
    Given I go to Sales / Opportunities
    When I click edit "Opportunity 1" in grid
    And I fill "Opportunity Form" with:
      | Expected Close Date | <Date:Sep 30, 2019>|
    And I save and close form
    Then I go to Sales / Opportunities
    And I should see Opportunity 1 in grid with following data:
      | EXPECTED CLOSE DATE | Sep 30, 2019 |
