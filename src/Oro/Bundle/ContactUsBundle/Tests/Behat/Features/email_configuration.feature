@regression
@ticket-BAP-16638
@ticket-BAP-17154

Feature: Email configuration
  In order to have ability to change Email configuration
  As an Administrator
  I want to be able to add mailbox with different actions

  Scenario: Add new mailbox
    Given I login as administrator
    And I go to System/Configuration
    And I follow "System Configuration/General Setup/Email Configuration" on configuration sidebar
    And I click "Add Mailbox"
    When I fill form with:
      | Mailbox Label | Test Mailbox     |
      | Email         | test@example.com |
    And I save form
    Then I should see "Test Mailbox has been saved" flash message

  Scenario: Make sure "Convert To Lead" action can be selected
    Given I click "Edit" on row "test@example.com" in grid
    When I select "Convert To Lead" from "Action"
    Then I should see "Owner"
    And I should see "Source"

  Scenario: Make sure "Convert To Case" action can be selected
    Given I select "Convert To Case" from "Action"
    Then I should see "Owner"
    And I should see "Assigned To"
