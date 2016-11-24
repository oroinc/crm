Feature: Managing Currency Rates
  In order to use other currencies in the application
  As an Administrator
  I want to be able to manage currency rates

  Scenario: Feature background
    Given I login as an Administrator
    And there are following currencies in the system:
      | BASE       | CURRENCY NAME     | CURRENCY CODE | CURRENCY SYMBOL | RATE FROM | RATE TO |
      | Checked    | US Dollar USD     | USD           | $               | 1         | 1       |
      | Unchecked  | Euro              | EUR           | €               | 1.09      | 0.91    |
      | Unchecked  | Ukrainian Hryvnia | UAH           | ₴               | 0.039     | 25.63   |
    When And I go to System/ Configuration
    And I click Currency
    Then I should see the folowing in the currency grid:
      | BASE       | CURRENCY NAME     | CURRENCY CODE | CURRENCY SYMBOL | RATE FROM | RATE TO |
      | Checked    | US Dollar USD     | USD           | $               | 1         | 1       |
      | Unchecked  | Euro              | EUR           | €               | 1.09      | 0.91    |
      | Unchecked  | Ukrainian Hryvnia | UAH           | ₴               | 0.039     | 25.63   |

  # Req ID OEE-996-1, OEE-996-2, OEE-996-4
  Scenario: Adding new currency rate
    Given I login as an Administrator
    And I go to System/ Configuration
    And I click Currency
    And I select "Irish Pound (IEP)" from "Allowed Currencies" dropdown
    And I press "Add" button
    Then I should see the following on Currency grid:
      | BASE      | CURRENCY NAME | CURRENCY CODE | CURRENCY SYMBOL | RATE FROM | RATE TO |
      | Unchecked | Irish Pound   | IEP           | IEP             |           |         |
    When I press "Save Settings" button
    Then for the "RATE FROM" field validation error "'Rate from' cannot be empty." is displayed
    And for the "Rate To" field validation error "'Rate to' cannot be empty." is displayed
    When I input "0" into "RATE FROM" field
    Then I see error message "'Rate from' must have positive value."
    When I input invalid symbols "hqhq" into "Rate from" field
    Then I see "'Rate from' must be a number." error message
    And when I fill out the "RATE FROM" field for "Irish Pound (IEP)" currency with "6"
    And I fill out the "RATE TO" field for "Irish Pound (IEP)" currency with "6.5"
    When I press "Save Settings" button
    Then I see "Configuration saved" flash message
    Then I should see the following on Currency grid:
      | BASE      | CURRENCY NAME | CURRENCY CODE | CURRENCY SYMBOL | RATE FROM | RATE TO |
      | Unchecked | Irish Pound   | IEP           | IEP             |     6     |    6.5  |

  #Req ID OEE-996-9
  Scenario: Editing currency rate by inline editing
    Given I fill out the the fields "Irish Pound (IEP)" currency with the following:
      | BASE      | CURRENCY NAME | CURRENCY CODE | CURRENCY SYMBOL | RATE FROM | RATE TO |
      | Unchecked | Irish Pound   | IEP           | IEP             |     5     |    5.5  |
    When I press "Save Settings" button
    Then I see "Configuration saved" flash message
    Then I should see the following on Currency grid:
      | BASE      | CURRENCY NAME | CURRENCY CODE | CURRENCY SYMBOL | RATE FROM | RATE TO |
      | Unchecked | Irish Pound   | IEP           | IEP             |     5     |    5.5  |
==============
  # Req ID OEE-996-15, OEE-996-16
  Scenario: Recalculating currency in Opportunity entity by choosing new base currency
    Given I create the Opportunity with the following details:
      | Opportunity Name  | Budget Amount | Close Revenue |
      | Mister Proper     | 1,000.00 USD  | 1,000.00 USD  |
    And I go to System/ Configuration
    And I click Currency
    And I fill out the fields for "Irish Pound (IEP)" currency with the following:
      | BASE    | CURRENCY NAME | CURRENCY CODE | CURRENCY SYMBOL | RATE FROM | RATE TO |
      | Checked | Irish Pound   | IEP           | IEP             |     5     |    5.5  |
    Then confirmation message is displayed: "You are about to change the base currency. By doing so, the values for
    your entire financial data using this currency will be recalculated. Do you want to continue?"
    And I press "OK"
    Then I should see the following on currency grid:
      | BASE    | CURRENCY NAME | CURRENCY CODE | CURRENCY SYMBOL | RATE FROM | RATE TO |
      | Checked | Irish Pound   | IEP           | IEP             |     1     |  1      |
    And I should see the United States USD currency with:
      | BASE      | CURRENCY NAME | CURRENCY CODE | CURRENCY SYMBOL | RATE FROM | RATE TO |
      | Unchecked | US Dollar USD | USD           | $               |     5.5   | 5       |
    When I press "Save Settings" button
    And I go to Sales/ Opportunities
    Then I should see Opportunity "Mister Proper" on grid with the following data:
      | Base Budget Amount  |
      | 5,500.00 IEP          |
    When I click on opportunity "Mister Proper"
    Then I should see the following:
      | Base Budget Amount  |
      | 5,500.00 IEP          |
    And when I press "Edit"
    Then I should see the following:
      | Base Budget Amount  |
      | 5,500.00 IEP          |

    # Req ID OEE-996-16
    # (not automated)
    Scenario: Recalculating currency in Dashboard widgets
    Given I go to Dashboard
    Then I should see on Opportunity By Lead Source widget corresponding values in IEP
    And I should see on Opportunity By Status widget corresponding values in IEP
    And I should see on Forecast widget corresponding values in IEP
    And I should see on Business Sales Chanel Statistics corresponding values in IEP
    And I should see on Opportunities List widget corresponding values in IEP

    # Req ID OEE-996-16
    # (not automated)
    Scenario: Recalculating currency in Reports
    Given I go to Reports & Segments/ Reports/ Opportunities/ Opportunities By Status
    Then I should see corresponding values in IEP
    When I go to Reports & Segments/ Reports/ Opportunities/ Total Forecast
    Then I should see corresponding values in IEP
    When I go to Reports & Segments/ Manage Segments
    And I press "Create Segments"
    And I create segment "Must see" with the following data:
      | Entity      | Type    |
      | Opportunity | Dynamic |
    And I choose the following columns: Opportunity > Base Budget Amount, Opportunity > Budget Amount Currency,
  Opportunity > Budget Amount Value, Opportunity > Close Revenue Currency, Opportunity > Close Revenue Value
    And I press "Save and Close" button
    Then I should see values for Base Budget Amount in IEP

   # Req ID OEE-996-3
  Scenario: Autofilling rate
    Given I go to System/ Configuration
    And I click Currency
    And I select "Argentine Austral (ARA)" from Allowed Currencies dropdown
    And I press "Add" button
    When I fill out the "Rate From" field with "10"
    Then "Rate To" field should be autofilled with "0.1" value
    When I delete values from "Rate To" and "Rate From" fields
    And I fill out the "Rate To" field with "10"
    Then "Rate From" field should be autofilled with "0.1" value
    And when I press "Save Settings" button
    Then I should see the following:
      | BASE      | CURRENCY NAME     | CURRENCY CODE | CURRENCY SYMBOL | RATE FROM | RATE TO |
      | Unchecked | Argentine Austral | ARA           | ARA             |     0.1   | 10      |

  #Req ID OEE-996-10, OEE-996-11, OEE-996-12, OEE-996-13, OEE-996-14
  Scenario: Testing UI to provide the user with better usability and user experience
    Given I go to System/ Configuration
    And I click Currency
    Then I should see that the tooltip for "Rate to" contains the following: "The conversion rate from the base
  currency to selected currency. Used to calculate new exchange rates when base currency is changed. Maximum precision
  is 10 digits."
    And the tooltip for "Rate From" is: "The conversion rate from selected currency to the base
  currency. Used to calculate transaction amounts (e.g. opportunity budget) in base currency if they were entered in
  other currencies. Maximum precision is 10 digits."
    And "Please, select currency" is displayed as a placeholder of currency add dropdown (above the rate management
  grid)
    And column names for the rate management grid are as follows: Base, Currency name, Currency code,
  Currency symbol, Rate from, Rate to, Sort, Actions
    And the "Delete" icon in the rate management grid is a cross sign

  # Req ID OEE-996-19
  Scenario: Deny deleting the currency that is used elsewhere in the system
    Given I delete Irish Pound currency from grid
    Then error message is displayed
    And when I reload the page
    Then I should see Irish Pound currency on currency grid

  # Req ID OEE-996-5, Req ID OEE-996-6, Req ID OEE-996-7, Req ID OEE-996-8
  Scenario Outline: Validating precision rate
    Given I add <Currency> and input into Rate From <Rate From Value>
    Then I should see in Rate to <Rate To Value>
    And when I add Botswanan Pula currency and input 3.12345678911 into Rate From field
    Then I should see 3.12345678911645456464 in the Rate From field
    And I should see 0.3201581029 in the Rate To field

    Examples:
      | Currency            | Rate From Value | Rate To Value |
      | Bermudan Dollar     | 3               | 0.333333      |
      | Barbadian Dollar    | 3.1             | 0.322581      |
      | Bangladeshi Taka    | 3.12            | 0.320513      |
      | Bulgarian Hard Lev  | 3.123           | 0.320205      |
      | Azerbaijani Manat   | 3.1234          | 0.320164      |
      | Bahraini Dinar      | 3.12345         | 0.320159      |
      | Bahamian Dollar     | 3.123456        | 0.320158      |
      | Austrian Schilling  | 3.1234567       | 0.3201581     |
      | Bolivian Peso       | 4.12345698      | 0.24251496    |
      | Bhutanese Ngultrum  | 3.123456789     | 0.320158103   |
      | Bolivian Mvdol      | 3.1234567891    | 0.3201581029  |

  # Req ID OEE-996-17, Req ID OEE-996-18
  Scenario: Deleting currency rate
    Given I click "Delete currency" for "Bolivian Mvdol (BOV)" currency on currency grid
    And the confirmation message "You are about to delete the currency. Do you want to continue?" is displayed and I
    confirm popup message
    Then I should not see the Bolivian Mvdol (BOV) currency on currency grid
    When I press "Save Settings" button
    Then I should not see the Bolivian Mvdol (BOV) currency on currency grid


