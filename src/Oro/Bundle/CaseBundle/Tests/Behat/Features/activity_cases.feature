@fixture-OroCaseBundle:case_crud.yml
Feature: Activity cases
  In order to keep track of all trouble cases
  As a Sales Manager
  I want to be able to keep a story of activity cases by CRUD them

  Scenario: Create case
    Given I login as administrator
    Then I go to Activities/ Cases
    And I click "Create Case"
    When I save and close form
    Then I should see validation errors:
      | Subject         | This value should not be blank.  |
    And I fill form with:
      | Subject         | Test case subject      |
      | Description     | Case for behat testing |
      | Resolution      | Create through form and check |
      | Assigned To     | John Doe               |
      | Source          | Web                    |
      | Status          | In Progress            |
      | Priority        | High                   |
      | Related Contact | Charlie Sheen          |
      | Related Account | Bruce                  |
    When I save and close form
    Then I should see Test case subject with:
      | Subject         | Test case subject      |
      | Description     | Case for behat testing |
      | Resolution      | Create through form and check |
      | Assigned To     | John Doe               |
      | Source          | Web                    |
      | Status          | In Progress            |
      | Priority        | High                   |
      | Related Contact | Charlie Sheen          |
      | Related Account | Bruce                  |

  Scenario: Edit case
    Given I click "Edit Case"
    When I fill in "Subject" with ""
    And I save and close form
    Then I should see validation errors:
      | Subject         | This value should not be blank.  |
    When I fill form with:
      | Subject         | Edited test case       |
      | Description     | Edited case for testing|
      | Resolution      | Edit through form      |
      | Assigned To     | John Doe               |
      | Source          | Other                  |
      | Status          | Resolved               |
      | Priority        | Normal                 |
      | Related Contact | Charlie Sheen          |
      | Related Account | Bruce                  |
    And I save and close form
    Then I should see Edited test case with:
      | Subject         | Edited test case       |
      | Description     | Edited case for testing|
      | Resolution      | Edit through form      |
      | Assigned To     | John Doe               |
      | Source          | Other                  |
      | Status          | Resolved               |
      | Priority        | Normal                 |
      | Related Contact | Charlie Sheen          |
      | Related Account | Bruce                  |

    Scenario: Case deletion
      Given I go to Activities/ Cases
      When I click Delete Predefined test case in grid
      And I confirm deletion
      Then I should see "Item deleted" flash message
      And there is one record in grid
      When I click view Edited test case in grid
      And I click "Delete Case"
      And I confirm deletion
      Then there is no records in grid
