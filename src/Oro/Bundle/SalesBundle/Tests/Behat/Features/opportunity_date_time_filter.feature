@ticket-BB-19935
@regression
@fixture-OroSalesBundle:OpportunityWithCreatedAtFixture.yml

Feature: Opportunity date time filter
  Check that the date time filter in different time zones works correctly for a list of opportunities.

  Scenario: Prepare datetime field for Opportunity entity
    Given I login as administrator
    And I go to System/Entities/Entity Management
    And filter Name as is equal to "Opportunity"
    And click View Opportunity in grid
    And I click on "Create Field"
    And I fill form with:
      | Field name | DateTimeField |
      | Type       | DateTime      |
    And I click "Continue"
    And I fill form with:
      | Show Grid Filter | Yes |
    When I save and close form
    And click update schema
    Then I should see Schema updated flash message

  Scenario: Update opportunities datetime field
    Given I go to Sales/ Opportunities
    When I click edit "Opportunity" in grid
    And fill "Opportunity Form" with:
      | DateTimeField | <DateTime:Dec 20, 2018, 11:00 AM> |
    And save and close form
    Then I should see "Opportunity saved" flash message

    When I go to Sales/ Opportunities
    Then I should see following grid:
      | Opportunity name | DateTimeField          |
      | Opportunity      | Dec 20, 2018, 11:00 AM |

  Scenario: Change time zone
    Given I go to System / Configuration
    And follow "System Configuration/General Setup/Localization" on configuration sidebar
    When uncheck "Use default" for "Timezone" field
    And fill form with:
      # UTC -02:00 America/Noronha
      | Timezone | America/Noronha |
    And save form
    Then I should see "Configuration saved" flash message

  Scenario: Check 'DateTime' filter
    Given I go to Sales/ Opportunities
    And I should see following grid:
      | Opportunity name | DateTimeField          |
      | Opportunity      | Dec 20, 2018, 09:00 AM |
    When I filter DateTimeField as between "Dec 20, 2018, 08:30 AM" and "Dec 20, 2018, 09:30 AM"
    Then there is one record in grid
