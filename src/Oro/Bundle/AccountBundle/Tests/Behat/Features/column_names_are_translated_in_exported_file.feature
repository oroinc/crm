@regression
@ticket-BB-19866
@fixture-OroContactBundle:LoadContactEntitiesFixture.yml
@fixture-OroAccountBundle:LoadAccountEntitiesFixture.yml

Feature: Column names are translated in exported file

  Scenario: Configure and switch to German localization
    Given I login as administrator
    When I go to System/Localization/Languages
    Then I should see English in grid with following data:
      | Status | Enabled |
    When I click "Add Language"
    And I select "German (Germany) - de_DE" from "Language"
    And I click "Add Language" in modal window
    Then I should see "Language has been added" flash message
    And I should see "German (Germany)" in grid with following data:
      | Status  | Disabled         |
      | Updates | Can be installed |
    When I click Enable "German (Germany)" in grid
    Then I should see "Language has been enabled" flash message
    And I should see German in grid with following data:
      | Status  | Enabled          |
      | Updates | Can be installed |
    When I click Install "German (Germany)" in grid
    Then I should see "UiDialog" with elements:
      | Title    | Install "German (Germany)" language |
      | okButton | Install                             |
    When I click "Install" in modal window
    Then I should see "German (Germany)" in grid with following data:
      | Status  | Enabled    |
      | Updates | Up to date |
    And I go to System/Localization/Localizations
    And I click "Create Localization"
    And fill "Localization Form" with:
      | Name                | German           |
      | Title               | German           |
      | Language            | German (Germany) |
      | Formatting          | German (Germany) |
    When I save and close form
    Then I should see "Localization has been saved" flash message
    And I go to System/Configuration
    And I follow "System Configuration/General Setup/Localization" on configuration sidebar
    And I fill form with:
      | Enabled Localizations | [English, German] |
      | Default Localization  | German            |
    And I submit form
    Then I should see "Configuration saved" flash message
    And I should see "Konfiguration"

  Scenario: Ensure column names are translated in the exported file
    # Customers/Accounts
    Given I go to Kunden/Konten
    When I click "Export"
    Then I should see "Der Export erfolgreich gestartet. Sie erhalten eine e-Mail-Benachrichtigung bei Fertigstellung." flash message
    And Email should contains the following "Export performed successfully. 2 accounts were exported. Download" text
    And Exported file for "Accounts" contains at least the following columns:
      | Id | Kontoname | Besitzer Benutzername | Standard Kontakt Vorname | Standard Kontakt Nachname | Kontakte 1 Vorname | Kontakte 1 Nachname | Beschreibung | Unternehmen Name | Empfehlung von Kontoname | Tags |
      | 1 | account_1 | admin | TestContact1 | TestContact1 | TestContact1 | TestContact1 |  | ORO |  |  |
      | 2 | account_2 | admin | TestContact2 | TestContact2 | TestContact2 | TestContact2 |  | ORO |  |  |
