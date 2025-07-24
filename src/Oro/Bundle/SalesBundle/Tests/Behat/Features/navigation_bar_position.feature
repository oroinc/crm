@regression
@ticket-BAP-16267

Feature: Navigation bar position
  In order to provide better navigation for users
  As a configurator
  I want to be sure that surfing is available despite the main menu position

  Scenario: Precondition
    When I login as administrator
    Then menu must be on left side
    And menu must be minimized

  Scenario Outline: Navigation through the minimized main menu with "Left" navigation bar position
    When I go to <menu>
    Then I should see <breadcrumb> in breadcrumbs

    Examples:
      | menu                               | breadcrumb                           |
      | Dashboards/ Manage Dashboards      | "Dashboards/ Manage Dashboards"      |
      | System/ Localization/ Translations | "System/ Localization/ Translations" |
      | Activities/ Calendar Events        | "Activities/ Calendar Events"        |

  Scenario: Filter menu items using search input
    When I select "Reports & Segments" in the side menu
    And I fill in "MenuSearch" with "To"
    Then I should see "Total Forecast"
    When I go to Reports & Segments/ Reports/ Opportunities/ Total Forecast
    Then I should see "Reports & Segments/ Reports/ Opportunities/ Total Forecast" in breadcrumbs

  Scenario: Create new menu item with empty url
    When I go to System/Menus
    And click view "application_menu" in grid
    And I drag and drop "ProductsItem" before "RootItem"
    And click "Application menu item"
    And I Create Menu Item
    And I fill "Menu Form" with:
      | Title    | Orange Item With Empty Url |
      | URI      | #                          |
      | Icon     | bolt                       |
    Then I save form
    When I click on "JS Tree item" with title "Orange Item With Empty Url"
    Then I Create Divider
    When I reload the page
    Then I should not see "Orange Item With Empty Url" in the "MainMenu" element
    And  I should see "Orange Item With Empty Url" after "System" in tree

  Scenario: Create new menu item
    And click "Application menu item"
    And I Create Menu Item
    And I fill "Menu Form" with:
      | Title    | Lime Item |
      | URI      | test-url  |
      | Icon     | bolt      |
    Then I save form
    When I reload the page
    Then I should see "Lime Item" in the "MainMenu" element
    And I should see "Lime Item" after "Orange Item With Empty Url" in tree

  Scenario: Create sub menu for custom menu item
    And click "Application menu item"
    And I Create Menu Item
    And I fill "Menu Form" with:
      | Title    | Apple Item |
      | URI      | #          |
      | Icon     | gear       |
    Then I save form
    Then I should not see "Apple Item" in the "MainMenu" element
    When I click on "JS Tree item" with title "Apple Item"
    And I should not see "Apple Item" in the "MainMenu" element
    And I Create Menu Item
    And I fill "Menu Form" with:
      | Title    | Mango Sub Item |
      | URI      | test           |
      | Icon     | gear           |
    Then I save form
    When I reload the page
    And I should see "Apple Item" in the "MainMenu" element

  Scenario: Check visibility for new menu items in expand main menu
    When I click "Main Menu Toggler"
    Then I should see "Lime Item" in the "MainMenu" element
    Then I should see "Apple Item" in the "MainMenu" element
    And I should not see "Orange Item With Empty Url" in the "MainMenu" element
    When I click "Main Menu Toggler"

  Scenario: Hide / show root category in main menu
    When click on "Dashboards menu item"
    And click "Hide"
    Then I reload the page
    And I should not see "Dashboards" in the "MainMenu" element
    And click "Show"
    Then I reload the page
    And I should see "Dashboards" in the "MainMenu" element

  Scenario: Updating main menu
    And I expand "Dashboards" in tree
    And click "Dashboards sub item"
    And click "Hide"
    And click on "Manage Dashboards sub item"
    And click "Hide"
    Then I reload the page
    And I should not see "Dashboards" in the "MainMenu" element
    And click "Application menu item"
    Then I Create Divider
    Then I reload the page
    And I should not see "Dashboards" in the "MainMenu" element
    And click on "Manage Dashboards sub item"
    And click "Show"
    Then I reload the page
    And I should see "Dashboards" in the "MainMenu" element

  Scenario: Expand main menu
    When I click "Main Menu Toggler"
    Then menu must be expanded
    When I click "Main Menu Toggler"
    Then menu must be minimized
    When click "Main Menu Toggler"
    Then menu must be expanded

  Scenario Outline: Navigation through the expanded main menu with "Left" navigation bar position
    When I go to <menu>
    Then I should see <breadcrumb> in breadcrumbs

    Examples:
      | menu                                | breadcrumb                            |
      | Dashboards/ Manage Dashboards       | "Dashboards/ Manage Dashboards"       |
      | System/ Localization/ Translations  | "System/ Localization/ Translations"  |
      | System/ Entities/ Entity Management | "System/ Entities/ Entity Management" |
      | Activities/ Calendar Events         | "Activities/ Calendar Events"         |

  Scenario Outline: Navigation through the main menu with "Left" navigation bar position with changing
                    of minimized/expanded mode on each step
    When I click "Main Menu Toggler"
    And go to <menu>
    Then I should see <breadcrumb> in breadcrumbs

    Examples:
      | menu                                | breadcrumb                            |
      | Activities/ Calendar Events         | "Activities/ Calendar Events"         |
      | Dashboards/ Manage Dashboards       | "Dashboards/ Manage Dashboards"       |
      | System/ Localization/ Translations  | "System/ Localization/ Translations"  |
      | System/ Entities/ Entity Management | "System/ Entities/ Entity Management" |
      | System/ Configuration               | "System/ Configuration" |

  Scenario: Change navigation bar position from "Left" to "Top"
    Given I login as administrator
    When I go to System/ Configuration
    And follow "System Configuration/General Setup/Display Settings" on configuration sidebar
    And uncheck "Use default" for "Position" field
    And select "Top" from "Position"
    And I save setting
    Then menu must be at top

  Scenario: Check menu items it the the main menu with "Top" navigation bar position
    Then I should see "Lime Item" in the "MainMenu" element
    Then I should see "Apple Item" in the "MainMenu" element
    And I should not see "Orange Item With Empty Url" in the "MainMenu" element

  Scenario: Delete Menu items:
    When I go to System/Menus
    And click view "application_menu" in grid
    When I click on "JS Tree item" with title "Orange Item With Empty Url"
    And click "Delete Menu Item"
    And click "Yes, Delete"
    Then I click on "JS Tree item" with title "Apple Item"
    And click "Delete Menu Item"
    And click "Yes, Delete"
    Then I click on "JS Tree item" with title "Lime Item"
    And click "Delete Menu Item"
    And click "Yes, Delete"
    When I reload the page
    And I should not see "Orange Item With Empty Url" in the "MainMenu" element
    And I should not see "Lime Item" in the "MainMenu" element
    And I should not see "Apple Item" in the "MainMenu" element
    And I should see "Mango Sub Item" in the "MainMenu" element

  Scenario Outline: Navigation through the main menu with "Top" navigation bar position
    When I go to <menu>
    Then I should see <breadcrumb> in breadcrumbs

    Examples:
      | menu                               | breadcrumb                           |
      | Dashboards/ Manage Dashboards      | "Dashboards/ Manage Dashboards"      |
      | System/ Localization/ Translations | "System/ Localization/ Translations" |
      | Activities/ Calendar Events        | "Activities/ Calendar Events"        |
