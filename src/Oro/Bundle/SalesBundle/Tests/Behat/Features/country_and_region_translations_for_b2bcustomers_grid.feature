@fixture-OroLocaleBundle:ZuluLocalization.yml
@fixture-OroAddressBundle:CountryNameTranslation.yml
@fixture-OroContactBundle:LoadContactEntitiesFixture.yml
@fixture-OroAccountBundle:LoadAccountEntitiesFixture.yml
@fixture-OroSalesBundle:LoadB2bCustomerEntitiesFixture.yml
Feature: Country and region translations for b2bcustomers grid
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
    When I show column Billing Address Country in grid
    And I show column Billing Address Region in grid
    And I show column Shipping Address Country in grid
    And I show column Shipping Address Region in grid
    Then should see following grid:
      | Customer name     | Billing Address Country | Billing Address Region | Shipping Address Country | Shipping Address Region |
      | BusinessCustomer1 | GermanyZulu             | BerlinZulu             | United StatesZulu        | FloridaZulu             |
      | BusinessCustomer2 | United StatesZulu       | FloridaZulu            | GermanyZulu              | BerlinZulu              |
    When I show filter "Billing Address Country" in "Business Customer Grid" grid
    And I show filter "Billing Address Region" in "Business Customer Grid" grid
    And I show filter "Shipping Address Country" in "Business Customer Grid" grid
    And I show filter "Shipping Address Region" in "Business Customer Grid" grid
    And I reload the page
    And I check "GermanyZulu" in Billing Address Country filter
    Then should see following grid:
      | Customer name     | Billing Address Country | Billing Address Region | Shipping Address Country | Shipping Address Region |
      | BusinessCustomer1 | GermanyZulu             | BerlinZulu             | United StatesZulu        | FloridaZulu             |
    And number of records in "Business Customer Grid" grid should be 1
    And I reset "Billing Address Country" filter
    When I check "GermanyZulu" in Shipping Address Country filter
    Then should see following grid:
      | Customer name     | Billing Address Country | Billing Address Region | Shipping Address Country | Shipping Address Region |
      | BusinessCustomer2 | United StatesZulu       | FloridaZulu            | GermanyZulu              | BerlinZulu              |
    And number of records in "Business Customer Grid" grid should be 1
    And I reset "Shipping Address Country" filter
    When filter Billing Address Region as contains "BerlinZulu"
    Then should see following grid:
      | Customer name     | Billing Address Country | Billing Address Region | Shipping Address Country | Shipping Address Region |
      | BusinessCustomer1 | GermanyZulu             | BerlinZulu             | United StatesZulu        | FloridaZulu             |
    And number of records in "Business Customer Grid" grid should be 1
    And I reset "Billing Address Region" filter
    When filter Shipping Address Region as contains "FloridaZulu"
    Then should see following grid:
      | Customer name     | Billing Address Country | Billing Address Region | Shipping Address Country | Shipping Address Region |
      | BusinessCustomer1 | GermanyZulu             | BerlinZulu             | United StatesZulu        | FloridaZulu             |
    And number of records in "Business Customer Grid" grid should be 1
    And I reset "Shipping Address Region" filter
    And I click edit "BusinessCustomer1" in grid
    When I fill "Business Customers Address Form" with:
      | Billing Address Country  | United StatesZulu |
      | Billing Address State    | FloridaZulu       |
      | Shipping Address Country | GermanyZulu       |
      | Shipping Address State   | BerlinZulu        |
    And I save and close form
    And go to Customers/  Business Customers
    And I show column Billing Address Country in grid
    And I show column Billing Address Region in grid
    And I show column Shipping Address Country in grid
    And I show column Shipping Address Region in grid
    Then should see following grid:
      | Customer name     | Billing Address Country | Billing Address Region | Shipping Address Country | Shipping Address Region |
      | BusinessCustomer1 | United StatesZulu       | FloridaZulu            | GermanyZulu              | BerlinZulu              |
      | BusinessCustomer2 | United StatesZulu       | FloridaZulu            | GermanyZulu              | BerlinZulu              |
