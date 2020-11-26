@regression
@ticket-CRM-8968
@ticket-BB-18953
@fixture-OroCampaignBundle:CampaignFixture.yml
@fixture-OroContactBundle:LoadContactEntitiesFixture.yml
@fixture-OroEmailBundle:templates.yml

Feature: Campaign sending
  In order to send email campaign
  As an Administrator
  I should be able to send emails to all recipients from email campaign

  Scenario: Send email campaign
    Given I login as administrator
    And I load Marketing List fixture
    And I should see Marketing/Email Campaigns in main menu
    And I go to Marketing/Email Campaigns
    Then I click "Create Email Campaign"
    And fill form with:
      | Name           | Test email campaign          |
      | Marketing List | Contact Email Marketing List |
    Then should see the following options for "Template" select:
      | test_template |
    And should not see the following options for "Template" select:
      | not_system_email_1 |
      | not_system_email_2 |
      | not_system_email_3 |
      | system_email       |
      | non_entity_related |
    Then I fill form with:
      | Campaign | Campaign 1    |
      | Template | test_template |
    And I save and close form
    Then I should see "Email campaign saved" flash message
    And I should see following grid:
      | Contact Email  |
      | test1@test.com |
      | test2@test.com |
    And number of records should be 2

    When I click "Send"
    Then I should see "Email campaign was sent" flash message
    When I go to Marketing/Email Campaigns
    And I click view Test email campaign in grid
    Then I should see following grid:
      | Contact Email  |
      | test1@test.com |
      | test2@test.com |
    And number of records should be 2

  Scenario: Check marketing list includes previously contacted members
    When I go to Marketing / Marketing Lists
    When I click view Contact Email Marketing List in grid
    Then I should see following grid:
      | Contact Email  |
      | test1@test.com |
      | test2@test.com |
    When I go to Marketing / Marketing Lists
    And I click edit Contact Email Marketing List in grid
    And I add the following filters:
      | Field Condition | First name | is equal to | TestContact1 |
    And I save and close form
    And I should see "Include Previously Contacted Members Yes"
    And I should see following grid:
      | Contact Email  |
      | test1@test.com |
      | test2@test.com |
    And number of records should be 2

  Scenario: Check marketing list does not include previously contacted members when Include Previously Contacted Members set to No
    When I go to Marketing / Marketing Lists
    And I click edit Contact Email Marketing List in grid
    And I fill form with:
      | Include Previously Contacted Members | false |
    And I save and close form
    And I should see "Include Previously Contacted Members No"
    And I should see following grid:
      | Contact Email  |
      | test1@test.com |
    And number of records should be 1

  Scenario: Check that sent email campaign stay unchanged
    When I go to Marketing/Email Campaigns
    When I click view Test email campaign in grid
    And I should see following grid:
      | Contact Email  |
      | test1@test.com |
      | test2@test.com |
    And number of records should be 2

  Scenario: Check that new email campaign sent to actual marketing list members only
    And I go to Marketing/Email Campaigns
    Then I click "Create Email Campaign"
    And fill form with:
      | Name           | Second email campaign        |
      | Marketing List | Contact Email Marketing List |
    Then should see the following options for "Template" select:
      | test_template |
    Then I fill form with:
      | Campaign | Campaign 1    |
      | Template | test_template |
    And I save and close form
    Then I should see "Email campaign saved" flash message
    And I should see following grid:
      | Contact Email  |
      | test1@test.com |
    And number of records should be 1

    When I click "Send"
    Then I should see "Email campaign was sent" flash message
    When I go to Marketing/Email Campaigns
    And I click view Second email campaign in grid
    And I should see following grid:
      | Contact Email  |
      | test1@test.com |
    And number of records should be 1
