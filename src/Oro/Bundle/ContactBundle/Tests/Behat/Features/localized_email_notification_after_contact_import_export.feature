@regression
@ticket-BAP-17336
@fixture-OroUserBundle:UserLocalizations.yml

Feature: Localized email notification after contact import export
  In order to import and export contact
  As an admin
  I should receive import/export emails in predefined language

  Scenario: Prepare configuration with different languages on each level
    Given I login as administrator
    And I go to System / Configuration
    And I follow "System Configuration/General Setup/Localization" on configuration sidebar
    And I fill form with:
      | Enabled Localizations | [English (United States), German Localization, French Localization] |
      | Default Localization  | French Localization                                                 |
    And I submit form
    Then I should see "Configuration saved" flash message
    When I go to System / User Management / Organizations
    And click Configuration "Oro" in grid
    And I follow "System Configuration/General Setup/Localization" on configuration sidebar
    And uncheck "Use System" for "Default Localization" field
    And I fill form with:
      | Default Localization | German Localization |
    And I submit form
    Then I should see "Configuration saved" flash message
    When I click My Configuration in user menu
    And I follow "System Configuration/General Setup/Localization" on configuration sidebar
    And uncheck "Use Organization" for "Default Localization" field
    And I fill form with:
      | Default Localization | English (United States) |
    And I submit form
    Then I should see "Configuration saved" flash message

  Scenario: A user should get an email in a language of its configuration after import of correct CSV file
    Given I go to System / Emails / Templates
    When I filter Template Name as is equal to "import_result"
    And I click "edit" on first row in grid
    And fill "Email Template Form" with:
      | Subject | English Import Result Subject |
      | Content | English Import Result Body    |
    And I click "French"
    And fill "Email Template Form" with:
      | Subject Fallback | false                        |
      | Content Fallback | false                        |
      | Subject          | French Import Result Subject |
      | Content          | French Import Result Body    |
    And I click "German"
    And fill "Email Template Form" with:
      | Subject Fallback | false                        |
      | Content Fallback | false                        |
      | Subject          | German Import Result Subject |
      | Content          | German Import Result Body    |
    And I submit form
    Then I should see "Template saved" flash message

    When go to Customers / Contacts
    And I download "Contacts" Data Template file
    And I fill template with data:
      | First Name | Last Name | Owner Id |
      | Some       | New       | 1        |
    And I import file
    Then Email should contains the following:
      | To      | admin@example.com             |
      | Subject | English Import Result Subject |
      | Body    | English Import Result Body    |

  Scenario: A user should get an email in a language of its configuration after validation of CSV file
    Given I go to System / Emails / Templates
    When I filter Template Name as is equal to "import_validation_result"
    And I click "edit" on first row in grid
    And fill "Email Template Form" with:
      | Subject | English Import Validation Result Subject |
      | Content | English Import Validation Result Body    |
    And I click "French"
    And fill "Email Template Form" with:
      | Subject Fallback | false                                   |
      | Content Fallback | false                                   |
      | Subject          | French Import Validation Result Subject |
      | Content          | French Import Validation Result Body    |
    And I click "German"
    And fill "Email Template Form" with:
      | Subject Fallback | false                                   |
      | Content Fallback | false                                   |
      | Subject          | German Import Validation Result Subject |
      | Content          | German Import Validation Result Body    |
    And I submit form
    Then I should see "Template saved" flash message

    When go to Customers / Contacts
    And I download "Contacts" Data Template file
    And I fill template with data:
      | First Name | Last Name | Owner Id |
      | Some Some  | New New   | 1        |
    And I validate file
    Then Email should contains the following:
      | To      | admin@example.com                        |
      | Subject | English Import Validation Result Subject |
      | Body    | English Import Validation Result Body    |

  Scenario: Prepare export and datagrid export templates
    Given I go to System / Emails / Templates
    When I filter Template Name as is equal to "export_result"
    And I click "edit" on first row in grid
    And fill "Email Template Form" with:
      | Subject | English Export Result Subject |
      | Content | English Export Result Body    |
    And I click "French"
    And fill "Email Template Form" with:
      | Subject Fallback | false                        |
      | Content Fallback | false                        |
      | Subject          | French Export Result Subject |
      | Content          | French Export Result Body    |
    And I click "German"
    And fill "Email Template Form" with:
      | Subject Fallback | false                        |
      | Content Fallback | false                        |
      | Subject          | German Export Result Subject |
      | Content          | German Export Result Body    |
    And I submit form
    Then I should see "Template saved" flash message
    When I go to System / Emails / Templates
    And I filter Template Name as is equal to "datagrid_export_result"
    And I click "edit" on first row in grid
    And fill "Email Template Form" with:
      | Subject | English Grid Export Result Subject |
      | Content | English Grid Export Result Body    |
    And I click "French"
    And fill "Email Template Form" with:
      | Subject Fallback | false                             |
      | Content Fallback | false                             |
      | Subject          | French Grid Export Result Subject |
      | Content          | French Grid Export Result Body    |
    And I click "German"
    And fill "Email Template Form" with:
      | Subject Fallback | false                             |
      | Content Fallback | false                             |
      | Subject          | German Grid Export Result Subject |
      | Content          | German Grid Export Result Body    |
    And I submit form
    Then I should see "Template saved" flash message

  Scenario: A user should get an export result email in a language of its configuration
    Given I click My Configuration in user menu
    When I follow "System Configuration/General Setup/Localization" on configuration sidebar
    And check "Use Organization" for "Default Localization" field
    And I submit form
    Then I should see "Configuration saved" flash message
    When go to Customers / Contacts
    And I click "Export"
    Then Email should contains the following:
      | To      | admin@example.com            |
      | Subject | German Export Result Subject |
      | Body    | German Export Result Body    |

  Scenario: A user should get grid export result email in a language of its configuration
    Given I go to System / User Management / Organizations
    When click Configuration "Oro" in grid
    And I follow "System Configuration/General Setup/Localization" on configuration sidebar
    And check "Use System" for "Default Localization" field
    And I submit form
    Then I should see "Configuration saved" flash message
    When go to Customers / Contacts
    And I click "Export Grid"
    And I click "CSV"
    Then Email should contains the following:
      | To      | admin@example.com                 |
      | Subject | French Grid Export Result Subject |
      | Body    | French Grid Export Result Body    |
