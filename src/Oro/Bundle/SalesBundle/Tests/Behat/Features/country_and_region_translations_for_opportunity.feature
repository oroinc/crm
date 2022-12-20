@fixture-OroSalesBundle:OpportunityFixture.yml
@fixture-OroLocaleBundle:ZuluLocalization.yml
@fixture-OroAddressBundle:CountryNameTranslation.yml
@fixture-OroContactBundle:LoadContactEntitiesFixture.yml

Feature: Country and region translations for opportunity
  In order to manage Opportunities
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

  Scenario: New Opportunity
    Given I open Opportunity Create page
    And fill form with:
      | Opportunity Name | Supper Opportunity |
      | Account          | mister customer 1  |
    And I click on "Contact create new"
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
    Then should see following "Select Contact" grid:
      | First name   | Last name    | Email                          | Phone      | Country           | State       | Zip/Postal Code |
      | Catherine    | Hinojosa     | CatherineJHinojosa@armyspy.com |            |                   |             |                 |
      | Charlie      | Sheen        | charlie@gmail.com              | 4157319375 | GermanyZulu       | BerlinZulu  |                 |
      | TestContact1 | TestContact1 | test1@test.com                 | 5556668888 | GermanyZulu       | BerlinZulu  | 10001           |
      | TestContact2 | TestContact2 | test2@test.com                 | 5556669999 | United StatesZulu | FloridaZulu | 10002           |
    When I click on Charlie in grid
    And save and close form
    Then I should see "Opportunity saved" flash message
    And I should see "Contact Charlie Sheen"
