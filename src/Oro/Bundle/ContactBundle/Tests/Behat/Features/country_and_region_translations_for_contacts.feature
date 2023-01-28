@regression
@fixture-OroLocaleBundle:ZuluLocalization.yml
@fixture-OroAddressBundle:CountryNameTranslation.yml
@fixture-OroContactBundle:LoadContactEntitiesFixture.yml
Feature: Country and region translations for contacts
  In order to manage Contacts
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

  Scenario: Check contacts UI
    Given go to Customers/ Contacts
    And number of records in "Contacts Grid" grid should be 2
    Then should see following grid:
      | First name   | Last name    | Email          | Phone      | Country           | State       | Zip/Postal Code |
      | TestContact1 | TestContact1 | test1@test.com | 5556668888 | GermanyZulu       | BerlinZulu  | 10001           |
      | TestContact2 | TestContact2 | test2@test.com | 5556669999 | United StatesZulu | FloridaZulu | 10002           |
    When I show filter "Country" in "Contacts Grid" grid
    And I show filter "State" in "Contacts Grid" grid
    And I check "United StatesZulu" in Country filter
    Then should see following grid:
      | First name   | Last name    | Email          | Phone      | Country           | State       | Zip/Postal Code |
      | TestContact2 | TestContact2 | test2@test.com | 5556669999 | United StatesZulu | FloridaZulu | 10002           |
    And number of records in "Contacts Grid" grid should be 1
    And I reset "Country" filter
    When filter State as contains "BerlinZulu"
    Then should see following grid:
      | First name   | Last name    | Email          | Phone      | Country     | State      | Zip/Postal Code |
      | TestContact1 | TestContact1 | test1@test.com | 5556668888 | GermanyZulu | BerlinZulu | 10001           |
    And number of records in "Contacts Grid" grid should be 1
    When click edit "TestContact1" in grid
    Then fill form with:
      | Country | United StatesZulu |
      | State   | FloridaZulu       |
    And save and close form
    When go to Customers/ Contacts
    Then should see following grid:
      | First name   | Last name    | Email          | Phone      | Country           | State       | Zip/Postal Code |
      | TestContact1 | TestContact1 | test1@test.com | 5556668888 | United StatesZulu | FloridaZulu | 10001           |
      | TestContact2 | TestContact2 | test2@test.com | 5556669999 | United StatesZulu | FloridaZulu | 10002           |

  Scenario: Add Address to contact
    Given I click view "TestContact1" in grid
    When I click "Add Address"
    And fill form with:
      | Primary         | true          |
      | Country         | GermanyZulu   |
      | State           | BerlinZulu    |
    And click "Save"
    Then contact has 2 addresses
    And GermanyZulu address must be primary
