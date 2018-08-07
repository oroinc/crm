@fixture-OroLocaleBundle:ZuluLocalization.yml
@fixture-OroAddressBundle:CountryNameTranslation.yml
@fixture-OroContactBundle:LoadContactEntitiesFixture.yml
@fixture-OroSalesBundle:LoadLeadEntitiesFixture.yml
Feature: Country and region translations for lead grid
  In order to manage Leads
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

  Scenario: Check Leads UI
    Given go to Sales/ Leads
    Then should see following grid:
      | Lead name | First name | Last name  | Email              | Phone number | Country           | State       | Postal Code |
      | testlead2 | ContactFN2 | ContactLN2 | testLead2@test.com | 5556669999   | United StatesZulu | FloridaZulu | 10004       |
      | testlead1 | ContactFN1 | ContactLN1 | testLead1@test.com | 5556668888   | GermanyZulu       | BerlinZulu  | 10003       |
    When I show filter "Country" in "Leads Grid" grid
    And I show filter "State" in "Leads Grid" grid
    And I reload the page
    And I check "United StatesZulu" in Country filter
    Then should see following grid:
      | Lead name | First name | Last name  | Email              | Phone number | Country           | State       | Postal Code |
      | testlead2 | ContactFN2 | ContactLN2 | testLead2@test.com | 5556669999   | United StatesZulu | FloridaZulu | 10004       |
    And number of records in "Leads Grid" grid should be 1
    And I reset "Country" filter
    When filter State as contains "BerlinZulu"
    Then should see following grid:
      | Lead name | First name | Last name  | Email              | Phone number | Country     | State      | Postal Code |
      | testlead1 | ContactFN1 | ContactLN1 | testLead1@test.com | 5556668888   | GermanyZulu | BerlinZulu | 10003       |
    And number of records in "Leads Grid" grid should be 1
    When click edit "testlead1" in grid
    Then fill form with:
      | Country | United StatesZulu |
      | State   | FloridaZulu       |
    And save and close form
    When go to Sales/ Leads
    Then should see following grid:
      | Lead name | First name | Last name  | Email              | Phone number | Country           | State       | Postal Code |
      | testlead2 | ContactFN2 | ContactLN2 | testLead2@test.com | 5556669999   | United StatesZulu | FloridaZulu | 10004       |
      | testlead1 | ContactFN1 | ContactLN1 | testLead1@test.com | 5556668888   | United StatesZulu | FloridaZulu | 10003       |
