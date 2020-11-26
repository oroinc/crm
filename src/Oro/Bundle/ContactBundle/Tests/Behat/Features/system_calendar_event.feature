@ticket-BAP-14474
@fixture-OroContactBundle:SystemCalendarEventFixture.yml
@fixture-OroCalendarBundle:ActivityListFixture.yml

Feature: System calendar event
  In order to have global system events
  As an Administrator
  I need to be able to manage system calendar events

  Scenario: Check displaying calendar event activity list item
    Given I login as administrator
    And I go to Customers/ Contacts
    And I click View Charlie Sheen in grid
    When I click on "Calendar Activity Item"
    Then I should see "System Calendar Event 1 Description"

  Scenario: Check calendar event activity list item form
    Given I hover on "Activity Dropdown Menu"
    When I click "Update Calendar event"
    Then "OroForm" must contains values:
      | Title       | System Calendar Event 1             |
      | Description | System Calendar Event 1 Description |
