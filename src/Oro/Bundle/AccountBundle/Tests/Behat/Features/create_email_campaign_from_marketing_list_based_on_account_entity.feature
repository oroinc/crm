@fixture-OroContactBundle:LoadContactEntitiesFixture.yml
@fixture-OroAccountBundle:LoadAccountEntitiesFixture.yml
@fixture-OroAccountBundle:LoadAccountEmailTemplate.yml
@ticket-BB-19484
@ticket-BB-21774
Feature: Create email campaign from marketing list based on Account entity
  As an administrator
  I want to send emails to the accounts using the email campaign functionality

  Scenario: Setup Email Campaign by Marketing list based on Accounts when both Accounts has a default contact
    Given I login as administrator
    And I go to Marketing/Marketing Lists
    Then I click "Create Marketing List"
    And I fill form with:
      | Name   | Account marketing list     |
      | Entity | Account                    |
      | Type   | Dynamic                    |
    And I add the following columns:
      | Contact Information |
      | Account name        |
    And I save and close form
    Then I should see "Marketing List saved" flash message
    And I should see "test1@test.com"
    And I should see "test2@test.com"
    Then I go to Marketing/Email Campaigns
    And I click "Create Email Campaign"
    Then I fill form with:
      | Name            | Account Email campaign  |
      | Marketing List  | Account marketing list  |
      | Schedule        | Manual                  |
    And should see the following options for "Template" select:
      | test_template   | no_entity_template      |
    Then I fill form with:
      | Transport       | Oro                     |
      | Template        | test_template           |
    And I save and close form
    Then I should see "Email Campaign saved" flash message
    And I should see "test1@test.com"
    And I should see "test2@test.com"
    And I click "Send"
    Then Email should contains the following:
      | To      | test1@test.com    |
      | Subject | Test Subject      |
    Then Email should contains the following:
      | To      | test2@test.com    |
      | Subject | Test Subject      |

  Scenario: Setup Email Campaign by Marketing list based on Accounts when only one Account has a default contact
    Given I login as administrator
    Then I go to Customers/Accounts
    And I click "edit" on row "account_1" in grid
    Then I click "Remove"
    And I save and close form
    And I go to Marketing/Marketing Lists
    Then I click "Create Marketing List"
    And I fill form with:
      | Name   | Test account marketing list  |
      | Entity | Account                      |
      | Type   | Dynamic                      |
    And I add the following columns:
      | Contact Information |
      | Account name        |
    And I save and close form
    Then I should see "Marketing List saved" flash message
    And I should not see "test1@test.com"
    And I should see "test2@test.com"
    Then I go to Marketing/Email Campaigns
    And I click "Create Email Campaign"
    Then I fill form with:
      | Name            | Test account Email campaign |
      | Marketing List  | Test account marketing list |
      | Schedule        | Manual                      |
    And should see the following options for "Template" select:
      | test_template   | no_entity_template          |
    Then I fill form with:
      | Transport       | Oro                         |
      | Template        | no_entity_template          |
    And I save and close form
    Then I should see "Email Campaign saved" flash message
    And I should not see "test1@test.com"
    And I should see "test2@test.com"
    And I click "Send"
    Then Email should not contains the following:
      | To      | test1@test.com         |
    Then Email should contains the following:
      | To      | test2@test.com         |
      | Subject | Test Subject No Entity |
