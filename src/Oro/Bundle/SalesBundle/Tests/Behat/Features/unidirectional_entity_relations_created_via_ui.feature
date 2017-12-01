@regression
@ticket-BAP-14504
@fixture-OroSalesBundle:lead.yml
Feature: Unidirectional entity relations created via UI
  In order to create custom field with type "Select"
  As an Administrator
  I want to have possibility to create custom select field and see value on grid

  Scenario: Create Select field for Lead entity
    Given I login as administrator
    When I go to System/ Entities/ Entity Management
    And I filter Name as is equal to "Lead"
    And I click view Lead in grid
    And I click "Create field"
    And I fill form with:
      | Field name   | TestField    |
      | Storage type | Table column |
      | Type         | Select       |
    And I click "Continue"
    And set Options with:
      | Label   |
      | Option1 |
      | Option2 |
    And I save and close form
    Then I should see "Field saved" flash message
    When I click update schema
    Then I should see Schema updated flash message

  Scenario: Update value of field for Lead entity
    Given I go to Sales/ Leads
    When I click edit Bruce in grid
    And I fill form with:
      | TestField | Option2 |
    And I save and close form
    Then I should see "Lead saved" flash message

    And I go to Sales/ Leads
    Then I should see Bruce in grid with following data:
      | Lead name | Bruce   |
      | TestField | Option2 |
