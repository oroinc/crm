@ticket-BAP-18004
@fixture-OroContactBundle:LoadContactEntitiesFixture.yml
@fixture-OroAccountBundle:LoadAccountEntitiesFixture.yml
Feature: Get account entity name
  In order to get account name
  As an Administrator
  I want to be able to get only the value of its Account Name field

  Scenario: Feature background
    Given I login as administrator
    When I go to System/ Entities/ Entity Management
    And I filter Name as is equal to "Account"
    And I click view Account in grid
    And I click "Create field"
    And I fill form with:
      | Field name   | TestField    |
      | Storage type | Table column |
      | Type         | Multi-Select |
    And I click "Continue"
    And set Options with:
      | Label   |
      | Option1 |
      | Option2 |
    And I save and close form
    Then I should see "Field saved" flash message

    When I click update schema
    Then I should see Schema updated flash message

  Scenario: Update value of field for Account entity
    Given I go to Customers/ Accounts
    When I click edit account_1 in grid
    And I fill form with:
      | TestField | Option2 |
    And I save and close form
    Then I should see "Account saved" flash message

  Scenario: Create Note with right account name field
    Given follow "More actions"
    When I click "Add note"
    Then "Note Form" must contains values:
      | Context | [account_1 (Account)] |

    When fill "Note Form" with:
      | Message | <strong>Standup</strong> |
    And I scroll to "Add Note Button"
    And I click "Add Note Button"
    Then I should see "Note saved" flash message
    And I should see "Standup" note in activity list

    When I collapse "Standup" in activity list
    Then I should see account_1 text in activity
    Then I should not see option2 text in activity
