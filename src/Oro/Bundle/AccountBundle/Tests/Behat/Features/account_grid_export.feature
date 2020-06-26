@fix-BAP-15754
@fix-BAP-14578
@fixture-OroAccountBundle:account-grid-export.yml
@fixture-OroUserBundle:manager.yml
Feature: Account grid export
  In order to get account grid data in text format
  As an Administrator
  I want to be able to export account via Management Console UI

  Scenario: Feature background
    Given sessions active:
      | Admin   | first_session  |
      | Manager | second_session |

  Scenario: Should be possible to export grid ordered by contact name column
    Given I proceed as the Manager
    And I login as "ethan" user
    And I go to Customers / Accounts
    And I should see following records in grid:
      | First_1 Last_1 |
      | First_2 Last_2 |
      | First_3 Last_3 |
      | First_4 Last_4 |
      | First_5 Last_5 |
    When I sort grid by "Contact name"
    And I should see "Export Grid"
    And I click "Export Grid"
    And I click "CSV"
    Then I should see "Export started successfully. You will receive email notification upon completion." flash message
    And Email should contains the following "Grid export performed successfully. Download" text

  Scenario: "Export Grid" button should be inaccessible if user have not enough privileges
    Given I proceed as the Admin
    And I login as administrator
    When I go to System/User Management/Roles
    And I click Edit Sales Manager in grid
    And I uncheck "Export Grid View" entity permission
    And I save form
    Then should see "Role saved" flash message
    When I proceed as the Manager
    And I reload the page
    Then I should not see "Export Grid"
    When I proceed as the Admin
    And I check "Export Grid View" entity permission
    And select following permissions:
      | Import/Export result | View:None |
    And I save form
    Then should see "Role saved" flash message
    When I proceed as the Manager
    And I reload the page
    Then I should not see "Export Grid"

  Scenario: "Export Entity" button should be inaccessible if user has not enough privileges
    Given I proceed as the Admin
    And select following permissions:
      | Import/Export result | View:Global |
    And I save form
    Then should see "Role saved" flash message
    When I proceed as the Manager
    And I go to Product/Products
    Then I should see "Export Products"
    When I proceed as the Admin
    And I uncheck "Export Entity Records" entity permission
    And I save form
    Then should see "Role saved" flash message
    When I proceed as the Manager
    And I reload the page
    Then I should not see "Export Products"
    When I proceed as the Admin
    And I check "Export Entity Records" entity permission
    And select following permissions:
      | Import/Export result | View:None |
    And I save form
    Then should see "Role saved" flash message
    When I proceed as the Manager
    And I reload the page
    Then I should not see "Export Products"
