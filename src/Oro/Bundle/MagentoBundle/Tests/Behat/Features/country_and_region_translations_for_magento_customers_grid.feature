# Magento integration is disabled in CRM-9202
@skip
@fixture-OroLocaleBundle:ZuluLocalization.yml
@fixture-OroAddressBundle:CountryNameTranslation.yml
@fixture-OroContactBundle:LoadContactEntitiesFixture.yml
@fixture-OroMagentoBundle:LoadMagentoEntitiesFixture.yml
Feature: Country and region translations for magento customers grid
  In order to manage Magento customer
  As a Administrator
  I want to see translated country and region names in UI

  Scenario: Feature Background
    Given I login as administrator
    And I go to System / Configuration
    And I follow "System Configuration/General Setup/Localization" on configuration sidebar
    And I fill form with:
      | Enabled Localizations | [English, Zulu_Loc] |
      | Default Localization  | Zulu_Loc            |
    And I submit form
    When I go to System / Localization / Translations
    And I click "Update Cache"
    Then I should see "Translation Cache has been updated" flash message

  Scenario: Check magento customers UI
    Given go to Customers/  Magento Customers
    Then should see following grid:
      | First name | Last name | Email           | Billing country   | Billing state |
      | firstName1 | lastName1 | email1@test.com | United StatesZulu | FloridaZulu   |
      | firstName2 | lastName2 | email2@test.com | GermanyZulu       | BerlinZulu    |
    When I show filter "Billing country" in "Magento Customer Grid" grid
    And I show filter "Billing state" in "Magento Customer Grid" grid
    And I check "GermanyZulu" in Billing country filter
    Then should see following grid:
      | First name | Last name | Email           | Billing country   | Billing state |
      | firstName2 | lastName2 | email2@test.com | GermanyZulu       | BerlinZulu    |
    And number of records in "Magento Customer Grid" grid should be 1
    And I reset "Billing country" filter in "Magento Customer Grid"
    When filter Billing state as contains "FloridaZulu"
    Then should see following grid:
      | First name | Last name | Email           | Billing country   | Billing state |
      | firstName1 | lastName1 | email1@test.com | United StatesZulu | FloridaZulu   |
    And number of records in "Magento Customer Grid" grid should be 1
