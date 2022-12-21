@ticket-BAP-21510
@fixture-OroContactBundle:LoadContactForGroupFixture.yml

Feature: Contact group CRUD
  In order to have the ability to work with contact groups
  As administrator
  I need to have the ability to create, view, update and delete contact group

  Scenario: Create contact group
    Given I login as administrator
    When I go to System/Contact Groups
    And click "Create Contact Group"
    And I fill form with:
      | Label | Contact Group Label |
    And save and close form
    Then I should see "Group saved" flash message

  Scenario: Edit contact group
    When I click "Edit" on row "Contact Group Label" in grid
    And fill form with:
      | Label | Contact Group Label Updated |
    And save and close form
    Then I should see "Group saved" flash message
    And I should see following grid containing rows:
      | Label                       |
      | Contact Group Label Updated |

  Scenario: Delete contact group
    When I click delete "Contact Group Label Updated" in grid
    And I confirm deletion
    Then I should see "Item deleted" flash message
    When I filter Label as is equal to "Contact Group Label Updated"
    Then there is no records in grid

  Scenario: Check filters of all contacts grid work well in contact group view page
    Given I reset "Label" filter
    When I click "Edit" on row "Sales Group" in grid
    Then number of records should be 1
    When I filter First Name as Contains "Test"
    Then number of records should be 1
    When I reset First Name filter
    And filter Last Name as Contains "Contact1"
    Then number of records should be 1
    And I reset Last Name filter
