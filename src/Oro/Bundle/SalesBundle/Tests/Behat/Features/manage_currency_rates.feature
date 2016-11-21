Feature: Managing Currency Rates
  In order to use other currencies in the application
  As an Administrator
  I want to be able to manage currency rates

  # Req ID OEE-996-1, OEE-996-2
  Scenario: Adding new currency rate
    Given I login as an Administrator
    And I go to System/ Configuration/ Currency
    And I select "Irish Pound (IEP)" from "Allowed Currencies" dropdown
    And I press "Add" button
    Then I should see the following on Currency grid:
      | CURRENCY NAME    | Irish Pound |
      | CURRENCY CODE    | IEP         |
      | CURRENCY SYMBOL  | IEP         |
    And "Base" radio button is not selected
    And "Rate From" field is empty
    And "Rate To" field is empty
    When I leave "Rate From" and "Rate To" fields empty and press "Save Settings" button
    Then for the "Rate From" field validation error "'Rate from' cannot be empty." is displayed
    And for the "Rate To" field validation error "'Rate to' cannot be empty." is displayed
    And when I fill out the "Rate From" field for "Irish Pound (IEP)" currency with "6"
    And I fill out the "Rate To" field for "Irish Pound (IEP)" currency with "6.5"
    When I press "Save Settings" button
    Then I see "Configuration saved" flash message
    And I should see "6" in the "Rate From" field for "Irish Pound (IEP)" currency
    And I should see "6.5" in the "Rate To" field for "Irish Pound (IEP)" currency

  Scenario: Viewing currency rate
    Given I go to System/ Configuration/ Currency
    Then I should see the following for the Irish Pound (IEP) currency on Currency grid:
      | CURRENCY NAME   | Irish Pound |
      | CURRENCY CODE   | IEP         |
      | CURRENCY SYMBOL | IEP         |
      | RATE FROM       | 6           |
      | RATE TO         | 6.5         |

  #Req ID OEE-996-9
  Scenario: Editing currency rate by inline editing
    Given I go to System/ Configuration/ Currency
    And the "Irish Pound (IEP)" currency is not a base currency
    And I fill out the "Rate From" field for "Irish Pound (IEP)" currency with "5"
    And I fill out the "Rate To" field for "Irish Pound (IEP)" currency with "5.5"
    When I press "Save Settings" button
    Then I see "Configuration saved" flash message
    And I should see "5" in the "Rate From" field for "Irish Pound (IEP)" currency
    And I should see "5.5" in the "Rate To" field for "Irish Pound (IEP)" currency

  # Req ID OEE-996-17, Req ID OEE-996-18
  Scenario: Deleting currency rate
    Given I go to System/ Configuration/ Currency
    And the "Irish Pound (IEP)" currency is not used in the system on organization or system level
    When I click the "cross" icon on currency grid
    And the confirmation message "You are about to delete the currency. Do you want to continue?" is displayed
    And I press "OK"
    And I press "Save Settings" button
    Then I should not see the Irish Pound (IEP) currency on currency grid

   # Req ID OEE-996-16
  Scenario: Checking that on setting new default currency, all multi-currency values are recalculated
    Given the United States Dollar USD is the base currency
    And I create the Opportunity with the following details:
      | Opportunity Name  | Budget Amount | Close Revenue |
      | Mister Proper     | 1,000.00 USD  | 1,000.00 USD  |
    And I go to System/ Configuration/ Currency
    And I add Swiss Franc CHF currency with the following rates:
      | Rate From | Rate To |
      | 2         | 0.5     |
    When I set Swiss Franc CHF as a base currency
    Then I see that the United States USD currency was recalculated:
      | Rate From | Rate To |
      | 0.5         | 2     |
    And I go to Sales/ Opportunities
    Then I should see Opportunity "Mister Proper" on grid
    And I should see "Base Budget Amount" = 500.00 CHF
    When I click on opportunity "Mister Proper"
    Then I should see "Base Budget Amount" = 500.00 CHF
    And when I press "Edit"
    Then I should see "Base Budget Amount" = 500.00 CHF
    When I go to Dashboard
    Then I should see on Opportunity By Lead Source widget corresponding values in CHF
    And I should see on Opportunity By Status widget corresponding values in CHF
    And I should see on Forecast widget corresponding values in CHF
    And I should see on Business Sales Chanel Statistics corresponding values in CHF
    And I should see on Opportunities List widget corresponding values in CHF
    When I go to Reports & Segments/ Reports/ Opportunities/ Opportunities By Status
    Then I should see corresponding values in CHF
    When I go to Reports & Segments/ Reports/ Opportunities/ Total Forecast
    Then I should see corresponding values in CHF
    When I go to Reports & Segments/ Manage Segments
    And I press "Create Segments"
    And I create segment "Must see" with the following data:
      | Entity      | Type    |
      | Opportunity | Dynamic |
    And I choose the following columns: Opportunity > Base Budget Amount, Opportunity > Budget Amount Currency,
  Opportunity > Budget Amount Value, Opportunity > Close Revenue Currency, Opportunity > Close Revenue Value
    And I press 'Save and Close" button
    Then I should see values for Base Budget Amount in CHF

   # Req ID OEE-996-3
  Scenario: Autofilling the value for the second corresponding field when the user adds new currency
    Given I go to System/ Configuration/ Currency
    And I select "Irish Pound (IEP)" from Allowed Currencies dropdown
    And I press "Add" button
    And I press "Save Settings" button
    When I fill out the "Rate From" field with "10"
    Then "Rate To" field should be autofilled with "0.1" value
    When I delete values from "Rate To" and "Rate From" fields
    And I fill out the "Rate To" field with "10"
    Then "Rate From" field should be autofilled with "0.1" value

  #Req ID OEE-996-4
  Scenario: Testing for input validation
    Given I go to System/ Configuration/ Currency
    And I select "Irish Pound (IEP)" on grid
    And I leave "Rate to" and "Rate from" fields blank
    And I press "Save Settings" button
    Then validation error for "Rate to" field "Rate to cannot be empty" is displayed
    And validation error for "Rate from" field "Rafe from cannot be empty" is displayed
    When I input "0" into "Rate from" field
    Then I see error message "'Rate from' must have positive value."
    When I input invalid symbols "hqhq" into "Rate from" field
    Then I see "'Rate from' must be a number." error message

  #Req ID OEE-996-10, OEE-996-11, OEE-996-12, OEE-996-13, OEE-996-14
  Scenario: Testing UI to provide the user with better usability and user experience
    Given I go to System/ Configuration/ Currency
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

  # Req ID OEE-996-15, Req ID OEE-996-20
  Scenario: Setting base currency
    Given Argentine Austral ARA currency and United States Dollar USD are added to the system:
      | Currency                  | Rate to |  Rate from  | Base currency |
      | United States Dollar USD  | 1       | 1           | Yes           |
      | Argentine Austral ARA     | 5       | 6           | No            |
    And I go to System/ Configuration/ Currency
    And I set Argentine Austral currency as base currency
    Then confirmation message is displayed: "You are about to change the base currency. By doing so, the values for
  your entire financial data using this currency will be recalculated. Do you want to continue?"
    And I press "OK"
    Then Argentine Austral currency is set as base currency
    And "Rate to" value is set to "1"
    And "Rate from" value is set to "1"


  # Req ID OEE-996-19
  Scenario: Deleting the currency if it is used elsewhere in the system
    Given I go to System/ Configuration/ Currency
    And I add Belarusian Ruble BYR currency
    And I set Belarusian Ruble BYR as a base currency
    And I create Opportunity with any data, where "Budget Amount" = 1,000.00 BYR, and "Close revenue" = 1,000.00 BYR
    When I delete Belarusian Ruble BYR currency from grid
    Then error message "Cannot remove "ALL" currency because it is used as a default in the following organizations:
  "OroCRM". Please change the default currency at organization level before removing it. Please reload the page to
  restore the last saved state." is displayed
    And when I reload the page
    Then I should see Belarusian Ruble BYR currency on currency grid

  # Req ID OEE-996-5, Req ID OEE-996-6, Req ID OEE-996-7, Req ID OEE-996-8
  Scenario: Validating precision rate
    Given I go to System/ Configuration/ Currency
    And I add <currency> and input into Rate From <Rate From value>
    Then I should see in Rate to <Rate To value>
      | currency            | Rate From value | Rate To value |
      | Bermudan Dollar     | 3               |  0.333333     |
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
    And when I add Botswanan Pula currency and input 3.12345678911 into Rate From field
    Then I should see 3.1234567891 in the Rate From field
    And I should see 0.3201581029 in the Rate To field




