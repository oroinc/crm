@fixture-OroLocaleBundle:ZuluLocalization.yml
@fixture-OroAddressBundle:CountryNameTranslation.yml
@fixture-OroContactBundle:LoadContactEntitiesFixture.yml
@fixture-OroAccountBundle:LoadAccountEntitiesFixture.yml
@fixture-OroSalesBundle:LoadB2bCustomerEntitiesFixture.yml
Feature: Country and region translations for customers leads grid
  In order to manage Business customers
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

  Scenario: Check Business customers UI
    Given go to Customers/  Business Customers
    And click view "BusinessCustomer1" in grid
    When I click "Leads" in scrollspy
    Then should see following "Business Customer Leads Grid" grid:
      | Lead Name  | First name     | Last name      | Country           | State       | Postal Code |
      | B2b Lead 2 | B2b ContactFN2 | B2b ContactLN2 | United StatesZulu | FloridaZulu | 10004       |
      | B2b Lead 1 | B2b ContactFN1 | B2b ContactLN1 | GermanyZulu       | BerlinZulu  | 10003       |
    When I show filter "Country" in "Business Customer Leads Grid" grid
    And I show filter "State" in "Business Customer Leads Grid" grid
    And I check "United StatesZulu" in Country filter in "Business Customer Leads Grid"
    Then should see following "Business Customer Leads Grid" grid:
      | Lead Name  | First name     | Last name      | Country           | State       | Postal Code |
      | B2b Lead 2 | B2b ContactFN2 | B2b ContactLN2 | United StatesZulu | FloridaZulu | 10004       |
    And I reset "Country" filter on grid "Business Customer Leads Grid"
    When filter State as contains "BerlinZulu" in "Business Customer Leads Grid"
    Then should see following "Business Customer Leads Grid" grid:
      | Lead Name  | First name     | Last name      | Country     | State      | Postal Code |
      | B2b Lead 1 | B2b ContactFN1 | B2b ContactLN1 | GermanyZulu | BerlinZulu | 10003       |
    When click edit "B2b Lead 1" in grid
    And fill form with:
      | Country | United StatesZulu |
      | State   | FloridaZulu       |
    And save and close form
    And go to Customers/  Business Customers
    And click view "BusinessCustomer1" in grid
    And I click "Leads" in scrollspy
    Then should see following "Business Customer Leads Grid" grid:
      | Lead Name  | First name     | Last name      | Country           | State       | Postal Code |
      | B2b Lead 2 | B2b ContactFN2 | B2b ContactLN2 | United StatesZulu | FloridaZulu | 10004       |
      | B2b Lead 1 | B2b ContactFN1 | B2b ContactLN1 | United StatesZulu | FloridaZulu | 10003       |
