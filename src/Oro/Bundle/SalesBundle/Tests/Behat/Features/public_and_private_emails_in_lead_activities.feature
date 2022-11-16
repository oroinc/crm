@ticket-CRM-9384
@regression
Feature: Public and private emails in lead activities
  In order to protect private conversation between users
  As an Administrator
  I should see only public emails in a lead activity list

  Scenario: Scenario background
    Given I login as "admin" user
    And I go to Sales/Leads
    And click "Create Lead"
    And I fill form with:
      | Lead Name  | Charlie Sheen         |
      | Emails     | [charlie@example.com] |
    When save and close form
    Then I should see "Lead saved" flash message

  Scenario: Should see public email in lead activity list
    Given I go to Sales/Leads
    And I click view "Charlie Sheen" in grid
    When I follow "More actions"
    And click "Send email"
    And fill "Send Email Form" with:
      | Subject | Good morning Charlie |
    And click "Send"
    Then I should see "The email was sent" flash message
    And I should see "Good morning Charlie" email in activity list

  Scenario: When an email become private email it should be removed from lead activity list
    Given I go to System/User Management/Users
    And click Edit admin in grid
    When fill form with:
      | Email | charlie@example.com |
    And I save and close form
    Then I should see "User saved" flash message
    When I go to Sales/Leads
    And I click view "Charlie Sheen" in grid
    Then I shouldn't see "Good morning Charlie" email in activity list

  Scenario: When an email become public email it should be added in lead activity list
    Given I go to System/User Management/Users
    And click Edit admin in grid
    When fill form with:
      | Email | admin@example.com |
    And I save and close form
    Then I should see "User saved" flash message
    When I go to Sales/Leads
    And I click view "Charlie Sheen" in grid
    Then I should see "Good morning Charlie" email in activity list
