@fixture-OroLocaleBundle:ZuluLocalization.yml
@fixture-OroAddressBundle:CountryNameTranslation.yml
@fixture-OroContactBundle:LoadContactEntitiesFixture.yml
Feature: Country and region translations for contacts select grid
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

  Scenario: Check Contacts UI
    Given I go to Customers/ Contacts
    And click "Create Contact"
    When click on "Reports to hamburger"
    And I click "maximize"
    Then should see following "Select Contact" grid:
      | First name   | Last name    | Email          | Phone      | Country           | State       | Zip/Postal Code |
      | TestContact1 | TestContact1 | test1@test.com | 5556668888 | GermanyZulu       | BerlinZulu  | 10001           |
      | TestContact2 | TestContact2 | test2@test.com | 5556669999 | United StatesZulu | FloridaZulu | 10002           |
    When I show filter "Country" in "Select Contact" grid
    And I show filter "State" in "Select Contact" grid
    And I check "GermanyZulu" in Country filter
    Then should see following "Select Contact" grid:
      | First name   | Last name    | Email          | Phone      | Country     | State      | Zip/Postal Code |
      | TestContact1 | TestContact1 | test1@test.com | 5556668888 | GermanyZulu | BerlinZulu | 10001           |
    And number of records in "Select Contact" grid should be 1
    And I reset "Country" filter
    When filter State as contains "FloridaZulu"
    Then should see following "Select Contact" grid:
      | First name   | Last name    | Email          | Phone      | Country           | State       | Zip/Postal Code |
      | TestContact2 | TestContact2 | test2@test.com | 5556669999 | United StatesZulu | FloridaZulu | 10002           |
    And number of records in "Select Contact" grid should be 1

  Scenario: Check creation of new contact via "Reports to - create new" button:
    Given I click "Close"
    When I click on "Reports to create new"
    And I click "maximize"
    And I fill "Create Contact Modal Form" with:
      | First name | Charlie             |
      | Last name  | Sheen               |
      | Emails     | [charlie@gmail.com] |
      | Phones     | [4157319375]        |
      | Primary    | true                |
      | Country    | GermanyZulu         |
      | State      | BerlinZulu          |
    And I click "Save" in modal window
    Then I should see "Saved successfully" flash message
    When I click on "Reports to hamburger"
    Then should see following "Select Contact" grid:
      | First name   | Last name    | Email             | Phone      | Country           | State       | Zip/Postal Code |
      | Charlie      | Sheen        | charlie@gmail.com | 4157319375 | GermanyZulu       | BerlinZulu  |                 |
      | TestContact1 | TestContact1 | test1@test.com    | 5556668888 | GermanyZulu       | BerlinZulu  | 10001           |
      | TestContact2 | TestContact2 | test2@test.com    | 5556669999 | United StatesZulu | FloridaZulu | 10002           |

