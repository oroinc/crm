@ticket-BAP-18091
@fixture-OroCRMBundle:DisplayInGridEntities.yml
@regression

Feature: New Field Display in Grid (CRM)
  In order to make sure that main entity grids respect "Display in Grid" setting
  As an Administrator
  I want to add a new field to a configurable entity, mark "Display in Grid" and check that field is appears on grid

  Scenario: Login to Admin Panel
    Given I login as administrator

  Scenario Outline: Add new field and mark as Display in Grid
    Given I go to System/Entities/Entity Management
    And I filter Name as Is Equal To "<Name>"
    And I check "<Module>" in Module filter
    And I click View <Name> in grid
    And I click "Create field"
    And I fill form with:
      | Field Name   | <Field>      |
      | Storage Type | Table column |
      | Type         | String       |
    And I click "Continue"
    And I save and close form
    Examples:
      | Name                       | Field     | Module                 |
      | Contact                    | TestField | OroContactBundle       |
      | Account                    | TestField | OroAccountBundle       |
      | Call                       | TestField | OroCallBundle          |
      | Task                       | TestField | OroTaskBundle          |
      | Opportunity                | TestField | OroSalesBundle         |
      | Lead                       | TestField | OroSalesBundle         |
      | B2bCustomer                | TestField | OroSalesBundle         |
      | ContactRequest             | TestField | OroContactUsBundle     |
      | ContactReason              | TestField | OroContactUsBundle     |
      | CaseEntity                 | TestField | OroCaseBundle          |

  Scenario: Update schema
    When I click update schema
    Then I should see "Schema updated" flash message

  Scenario Outline: Check new field in grid settings
    Given I go to System/Entities/Entity Management
    And I filter Name as Is Equal To "<Name>"
    And I check "<Module>" in Module filter
    And I click View <Name> in grid
    And I click "Number of records"
    When click "Grid Settings"
    Then I should see following columns in the grid settings:
      | <Field> |
    Examples:
      | Name                       | Field     | Module                 |
      | Contact                    | TestField | OroContactBundle       |
      | Account                    | TestField | OroAccountBundle       |
      | Call                       | TestField | OroCallBundle          |
      | Task                       | TestField | OroTaskBundle          |
      | Opportunity                | TestField | OroSalesBundle         |
      | Lead                       | TestField | OroSalesBundle         |
      | B2bCustomer                | TestField | OroSalesBundle         |
      | ContactRequest             | TestField | OroContactUsBundle     |
      | ContactReason              | TestField | OroContactUsBundle     |
      | CaseEntity                 | TestField | OroCaseBundle          |
