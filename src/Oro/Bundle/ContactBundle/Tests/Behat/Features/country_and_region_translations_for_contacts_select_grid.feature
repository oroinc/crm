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
    And I follow "System Configuration/General Setup/Language Settings" on configuration sidebar
    And I fill form with:
      | Supported Languages | [English, Zulu] |
      | Use Default         | false           |
      | Default Language    | Zulu            |
    And I submit form
    When I go to System / Localization / Translations
    And I click "Update Cache"
    Then I should see "Translation Cache has been updated" flash message

  Scenario: Check Contacts UI
    Given I go to Customers/ Contacts
    And click "Create Contact"
    When click on "Reports to hamburger"
    And I click "maximize"
    Then should see following "Select Reports to" grid:
      | First name   | Last name    | Email          | Phone      | Country           | State       | Zip/Postal Code |
      | TestContact1 | TestContact1 | test1@test.com | 5556668888 | GermanyZulu       | BerlinZulu  | 10001           |
      | TestContact2 | TestContact2 | test2@test.com | 5556669999 | United StatesZulu | FloridaZulu | 10002           |
    When I show filter "Country" in "Select Reports to" grid
    And I show filter "State" in "Select Reports to" grid
    And I check "GermanyZulu" in Country filter
    Then should see following "Select Reports to" grid:
      | First name   | Last name    | Email          | Phone      | Country     | State      | Zip/Postal Code |
      | TestContact1 | TestContact1 | test1@test.com | 5556668888 | GermanyZulu | BerlinZulu | 10001           |
    And number of records in "Select Reports to" grid should be 1
    And I reset "Country" filter
    When filter State as contains "FloridaZulu"
    Then should see following "Select Reports to" grid:
      | First name   | Last name    | Email          | Phone      | Country           | State       | Zip/Postal Code |
      | TestContact2 | TestContact2 | test2@test.com | 5556669999 | United StatesZulu | FloridaZulu | 10002           |
    And number of records in "Select Reports to" grid should be 1
