@ticket-BAP-21510

Feature: Channel CRUD
  In order to get access to the navigation items related to the channels
  As an Administrator
  I need to have the ability to create, view, update and delete channels

  Scenario: Create channel
    Given I login as administrator
    And I go to System / Channels
    And I click "Create Channel"
    When I fill form with:
      | Name         | Simple channel |
      | Channel type | Custom         |
    And I should see "Customer identity"
    And save and close form
    Then I should see "Channel saved" flash message
    And I should see channels with:
      | Name         | Simple channel |
      | Channel type | Custom         |

  Scenario: Edit channel
    When I click "Edit"
    Then I fill form with:
      | Name | Simple channel update |
    And save and close form
    And I should see "Channel saved" flash message

  Scenario: View channel in grid
    When I go to System / Channels
    Then I should see following grid containing rows:
      | Name                  | Channel type |
      | Simple channel update | Custom       |
    And I filter Name as is equal to "Not found"
    And there is no records in grid
    And I reset Name filter

  Scenario: Delete channel
    When I click Delete Simple channel update in grid
    And I confirm deletion
    Then I should see "Item deleted" flash message
    And there is 1 records in grid
    And I should not see "Simple channel update"
