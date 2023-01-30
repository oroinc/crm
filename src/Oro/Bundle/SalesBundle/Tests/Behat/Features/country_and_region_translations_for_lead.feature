@regression
@fixture-OroLocaleBundle:ZuluLocalization.yml
@fixture-OroAddressBundle:CountryNameTranslation.yml
@fixture-OroContactBundle:LoadContactEntitiesFixture.yml
@fixture-OroSalesBundle:LoadLeadEntitiesFixture.yml
Feature: Country and region translations for lead
  In order to manage Leads
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

  Scenario: Add Address to existing lead
    Given I click view "testlead1" in grid
    When I click "Add Address"
    And fill form with:
      | Primary         | true          |
      | Country         | GermanyZulu   |
      | State           | BerlinZulu    |
    And click "Save"
    Then two addresses should be in page
    And GermanyZulu address must be primary

  Scenario: Create lead with two addresses
    Given I open Lead Create page
    When I click on "Contact create new"
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
    When I click on "Contact hamburger"
    Then I should see following "Select Contact" grid:
      | First name   | Last name    | Email             | Phone      | Country           | State       | Zip/Postal Code |
      | Charlie      | Sheen        | charlie@gmail.com | 4157319375 | GermanyZulu       | BerlinZulu  |                 |
      | TestContact1 | TestContact1 | test1@test.com    | 5556668888 | GermanyZulu       | BerlinZulu  | 10001           |
      | TestContact2 | TestContact2 | test2@test.com    | 5556669999 | United StatesZulu | FloridaZulu | 10002           |
    When I click on Charlie in grid
    And I click "Add Address"
    And I fill "Lead Form" with:
      | Lead Name      | lead with two addresses |
      | Country        | United StatesZulu       |
      | State          | FloridaZulu             |
      | Second Country | GermanyZulu             |
      | Second State   | BerlinZulu              |
    And I save and close form
    Then I should see "Lead saved" flash message
    And two addresses should be in page
    And FL US address must be primary
