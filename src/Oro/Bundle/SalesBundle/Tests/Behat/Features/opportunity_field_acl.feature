@fixture-OroSalesBundle:OpportunityFixture.yml

Feature: Opportunity field ACL
  In order to have ability to modify opportunities with field ACL protection
  As an user
  I should not have broken data after editing an entity with disabled field

  Scenario: Enable Field ACL for accounts and modify role
    Given I login as administrator
    And I go to System/Entities/Entity Management
    And filter Name as is equal to "Opportunity"
    When I click Edit Opportunity in grid
    And check "Field Level ACL"
    And save and close form
    Then I should see "Entity saved" flash message
    And I go to System/User Management/Roles
    And I filter Label as is equal to "Administrator"
    And I click Edit Administrator in grid
    When I expand "Opportunity" permissions in "Entity" section
    And I select following field permissions:
      | Customer need | View:Global | Edit:None |
    And I save and close form
    Then I should see "Role saved" flash message

  Scenario: Edit opportunity
    Given I go to Sales/Opportunities
    When I click edit Opportunity 1 in grid
    And I fill form with:
      | Opportunity Name | edited opportunity |
    And I save and close form
    And I should see "some needs"
