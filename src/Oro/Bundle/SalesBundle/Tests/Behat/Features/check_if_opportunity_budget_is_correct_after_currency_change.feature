@fixture-OroSalesBundle:OpportunityWithBudgetFixture.yml
@fixture-OroLocaleBundle:DutchLocalization.yml
Feature: Check if Opportunity budget is correct after currency change
  In order to have correct values
  as a Administrator
  I should have a possibility to change default currency and see correct Opportunity budget in new currency

  Scenario: Administrator check current opportunity budget
    Given I login as administrator
    And I go to Sales/Opportunities
    Then I should see following grid:
      | Opportunity Name | Budget Amount | Budget Amount ($) |
      | Opportunity 1    | $50.00        | $50.00            |

  Scenario: Change default currency and check converted budget
    Given I go to System/Configuration
    And I follow "System Configuration/General Setup/Localization" on configuration sidebar
    And I fill "Configuration Localization Form" with:
      | Enabled Localizations        | Dutch       |
      | Default Localization         | Dutch       |
      | Primary Location Use Default | false       |
      | Primary Location             | Netherlands |
    And I click "Save settings"
    And I should see "Configuration saved" flash message
    And I follow "System Configuration/General Setup/Currency" on configuration sidebar
    And I click "EuroAsDefaultValue"
    And I click "Yes"
    And I click "Save settings"
    And I go to Sales/Opportunities
    Then I should see "€ 50,00"
