@ticket-BAP-15156
Feature: Opportunity Management Flow with role permissions
  In order to have roadmap for opportunity flow
  As an Administrator
  I want to be able to manage permissions for Opportunity workflow

  Scenario: New Opportunity
    Given I login as administrator
    And there is following Account:
      | name |
      | Acme |
    When I open Opportunity Create page
    And I fill form with:
      | Opportunity Name | Summer sales |
      | Account          | Acme         |
    And I save and close form
    Then I should see "Opportunity saved" flash message

  Scenario: Enable and start Opportunity Management Flow
    Given I go to System/ Workflows
    And I click on Opportunity Management Flow in grid
    And I click "Activate"
    And I click "Activate" in modal window
    When I go to Sales/ Opportunities
    And I click "view" on row "Summer sales" in grid
    And I click "Start Opportunity Management Flow"
    And I click "Submit"
    Then I should see "Opportunity Management Flow"
    And I should see "Develop"
    And I should see "Close as Won"
    And I should see "Close as Lost"

  Scenario: Workflow with disabled performing transitions
    Given I go to System/ User Management/ Roles
    And I click edit Administrator in grid
    And select following permissions:
      | Opportunity Management Flow | Perform transitions:None |
    And I save and close form
    When I go to Sales/ Opportunities
    And I click "view" on row "Summer sales" in grid
    Then I should not see "Develop"
    And I should not see "Close as Won"
    And I should not see "Close as Lost"

  Scenario: Workflow with disabled view
    Given I go to System/ User Management/ Roles
    And I click edit Administrator in grid
    And I select following permissions:
      | Opportunity Management Flow | View Workflow:None |
    And I save and close form
    When I go to Sales/ Opportunities
    And I click "view" on row "Summer sales" in grid
    Then I should not see "Opportunity Management Flow"
