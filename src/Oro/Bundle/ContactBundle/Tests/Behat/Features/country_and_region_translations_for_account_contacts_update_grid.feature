@fixture-OroLocaleBundle:ZuluLocalization.yml
@fixture-OroAddressBundle:CountryNameTranslation.yml
@fixture-OroContactBundle:LoadContactEntitiesFixture.yml
Feature: Country and region translations for account contacts update grid
  In order to manage Accounts
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

  Scenario: Check accounts UI
    Given go to Customers/ Accounts
    And click "Create Account"
    When click "Add"
    Then should see following "Select Contacts Grid" grid:
      | First name   | Last name    | Email          | Phone      | Country           | State       | Zip/Postal Code |
      | TestContact1 | TestContact1 | test1@test.com | 5556668888 | GermanyZulu       | BerlinZulu  | 10001           |
      | TestContact2 | TestContact2 | test2@test.com | 5556669999 | United StatesZulu | FloridaZulu | 10002           |
    When I show filter "Country" in "Select Contacts Grid" grid
    And I show filter "State" in "Select Contacts Grid" grid
    And I check "GermanyZulu" in Country filter
    Then should see following "Select Contacts Grid" grid:
      | First name   | Last name    | Email          | Phone      | Country     | State      | Zip/Postal Code |
      | TestContact1 | TestContact1 | test1@test.com | 5556668888 | GermanyZulu | BerlinZulu | 10001           |
    And number of records in "Select Contacts Grid" grid should be 1
    And I reset "Country" filter
    When filter State as contains "FloridaZulu"
    Then should see following "Select Contacts Grid" grid:
      | First name   | Last name    | Email          | Phone      | Country           | State       | Zip/Postal Code |
      | TestContact2 | TestContact2 | test2@test.com | 5556669999 | United StatesZulu | FloridaZulu | 10002           |
    And number of records in "Select Contacts Grid" grid should be 1
