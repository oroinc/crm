@regression
@ticket-CRM-9208

Feature: Country filter
  In order to handle select filters with both non-render-able and lazy loading mode perfectly
  As a Administrator
  I create lead records and check if country filter has a nice interaction at any operation in the datagrid

  Scenario: Login backoffice
    Given I login as "admin" user

  Scenario Outline: Add some lead records
    When I go to Sales/Leads
    And click "Create Lead"
    And fill form with:
      | Lead Name | <Lead Name> |
      | Country   | <Country>  |
    And save and close form
    Then I should see "Lead saved" flash message
    Examples:
      | Lead Name             | Country       |
      | Semen Marchenko      | Ukraine       |
      | Oleksandr Vashchenko | Ukraine       |
      | Otto Contreras       | United States |
      | Kai Hsuan Song       | Taiwan        |

  Scenario: Check county filter
    Given I go to Sales/Leads
    Then I should not see "Country" filter in grid
    When I show filter "Country" in "Leads Grid" grid
    Then I should see "Country" filter in grid
    When I check "Ukraine" in Country filter
    Then number of records in "Leads Grid" grid should be 2
    And should see following grid:
      | Lead name            | Country |
      | Oleksandr Vashchenko | Ukraine |
      | Semen Marchenko      | Ukraine |
    When I check "United States" in Country filter
    Then number of records in "Leads Grid" grid should be 1
    And should see following grid:
      | Lead name      | Country       |
      | Otto Contreras | United States |
    When I reset "Country" filter
    Then number of records in "Leads Grid" grid should be 4
    When I hide filter "Country" in "Leads Grid" grid
    Then I should not see "Country" filter in grid
