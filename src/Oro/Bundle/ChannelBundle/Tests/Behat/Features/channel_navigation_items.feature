@ticket-CRM-9244

Feature: Channel Navigation Items
  In order to get access to the navigation items related to the channels
  As an Administrator
  I should have a possibility to configure channel with entities to unlock navigation items

  Scenario: Create channel
    Given I login as administrator
    And I go to System / Channels
    And I click "Create Channel"
    When I fill form with:
      | Name         | Business Channel |
      | Channel type | Custom           |
    And save form
    Then I should see "Channel saved" flash message
    And I should not see "Customers / Business Customer" in the "MainMenu" element

  Scenario: Update channel
    Given I fill "Channel Form" with:
      | Entities Select | Business Customer |
    And click "Add"
    When I save form
    Then I should see "Channel saved" flash message
    And I should see Customers / Business Customers in main menu
