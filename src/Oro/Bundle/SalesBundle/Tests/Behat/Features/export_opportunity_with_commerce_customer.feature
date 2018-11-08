@ticket-BB-15488
Feature: Export opportunity with commerce customer
  In order to use export functionality for opportunity entity
  As an admin
  I should be able to export opportunity with commerce customer as account relation

  Scenario: Create commerce customer entity and opportunity with customer as account
    Given I login as administrator
    When I go to Customers / Customers
    And I click "Create Customer"
    And I fill form with:
      | Name | Test customer |
    And I save and close form
    Then I should see "Customer has been saved" flash message
    When I go to Sales / Opportunities
    And I click "Create Opportunity"
    And I fill form with:
      | Opportunity Name | Test opportunity         |
      | Account          | Test customer (Customer) |
    And I save and close form
    Then I should see "Opportunity saved" flash message

  Scenario: Opportunity export with commerce customer should be successful
    When I go to Sales / Opportunities
    And I click "Export"
    Then I should see "Export started successfully. You will receive email notification upon completion." flash message
    And Email should contains the following:
      | To      | admin@example.com              |
      | Body    | Export performed successfully. |
