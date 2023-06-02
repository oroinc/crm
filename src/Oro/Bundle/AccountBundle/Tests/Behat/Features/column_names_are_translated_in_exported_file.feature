@regression
@ticket-BB-19866
@ticket-BB-22241
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
      | Name       | German           |
      | Title      | German           |
      | Language   | German (Germany) |
      | Formatting | German (Germany) |
    When I save and close form
    Then I should see "Localization has been saved" flash message
    And I should see "Translation cache update is required. Click here to update" flash message
    Then I click "Click here"
    When I click "Update Cache"
    Then I should see "Translation Cache has been updated"
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
      | 1  | account_1 | admin                 | TestContact1             | TestContact1              | TestContact1       | TestContact1        |              | ORO              |                          |      |
      | 2  | account_2 | admin                 | TestContact2             | TestContact2              | TestContact2       | TestContact2        |              | ORO              |                          |      |

  Scenario: Import products with headers in German localization
    When I go to Produkte/Produkte
    And I click "Import Datei"
    And I upload "import_template_de.csv" file to "Import File Field"
    And I click "Import Datei"
    Then I should see "Import erfolgreich gestartet. Sie erhalten eine E-Mail-Benachrichtigung nach Fertigstellung." flash message
    When I wait for 3 seconds
    And reload the page
    Then should see following grid:
      | ART.-NR. | NAME         | PRODUKT FAMILIE | STATUS    | LAGERBESTAND-STATUS |
      | sku_001  | Product Name | Default         | Aktiviert | In Stock            |
    When I click "Produkte exportieren"
    Then I should see "Der Export erfolgreich gestartet. Sie erhalten eine e-Mail-Benachrichtigung bei Fertigstellung." flash message
    And Email should contains the following "Export performed successfully. 1 products were exported. Download" text
    And Exported file for "Products" with processor "oro_product_product" contains at least the following columns:
      | Produkt Familie.Code | Art.-Nr. | Status  | Typ    | Einheitsmenge.Einheit.Code | Lagerbestand-Status.ID | Einheitsmenge.Präzision | Einheitsmenge.Konversionsrate | Einheitsmenge.Verkaufen | additionalUnitPrecisions:0:unit:code | additionalUnitPrecisions:0:precision | additionalUnitPrecisions:0:conversionRate | additionalUnitPrecisions:0:sell | Name.default.fallback | Name.default.value | Name.English (United States).fallback | Name.English (United States).value | Name.German.fallback | Name.German.value | Kurze Beschreibung.default.fallback | Kurze Beschreibung.default.value | Kurze Beschreibung.English (United States).fallback | Kurze Beschreibung.English (United States).value | Kurze Beschreibung.German.fallback | Kurze Beschreibung.German.value | Beschreibung.default.fallback | Beschreibung.default.value | Beschreibung.English (United States).fallback | Beschreibung.English (United States).value | Beschreibung.German.fallback | Beschreibung.German.value | Konfigurierbare Attribute | Verfügbarkeitsdatum | Empfehlungen | Neuheiten | Lieferrückstände.value | Kategorie.ID | Lagerbestand verringern.value | Niedrigen Lagerbestand markieren.value | Lager Schwellwert.value | Niedriger Lagerbestand-Schwellenwert.value | Verwalteter Lagerbestand.value | Maximalbestellmenge.value | Meta-Beschreibung.default.fallback | Meta-Beschreibung.default.value | Meta-Beschreibung.English (United States).fallback | Meta-Beschreibung.English (United States).value | Meta-Beschreibung.German.fallback | Meta-Beschreibung.German.value | Meta-Schlüsselwörter.default.fallback | Meta-Schlüsselwörter.default.value | Meta-Schlüsselwörter.English (United States).fallback | Meta-Schlüsselwörter.English (United States).value | Meta-Schlüsselwörter.German.fallback | Meta-Schlüsselwörter.German.value | Meta-Titel.default.fallback | Meta-Titel.default.value | Meta-Titel.English (United States).fallback | Meta-Titel.English (United States).value | Meta-Titel.German.fallback | Meta-Titel.German.value | Mindestbestellmenge.value | Demnächst.value | URL Slug.default.fallback | URL Slug.default.value | URL Slug.English (United States).fallback | URL Slug.English (United States).value | URL Slug.German.fallback | URL Slug.German.value | category.default.title |
      | default_family       | sku_001  | enabled | simple | kg                         | in_stock               | 3                       | 1                             | 1                       | item                                 | 0                                    | 5                                         | 0                               |                       | Product Name       |                                       |                                    |                      |                   |                                     | Product Short Description        |                                                     |                                                  |                                    |                                 |                               | Product Description        |                                               |                                            |                              |                           |                           | 19.04.2023 00:00:00 | 0            | 0         | systemConfig           |              | systemConfig                  | systemConfig                           | systemConfig            | systemConfig                               | systemConfig                   | systemConfig              |                                    |                                 |                                                    |                                                 |                                   |                                |                                       |                                    |                                                       |                                                    |                                      |                                   |                             |                          |                                             |                                          |                            |                         | systemConfig              | 0               |                           | product-name           |                                           |                                        |                          |                       |                        |
