@fixture-OroSalesBundle:opportunity_from_related.yml
Feature: Create Opportunity from related entity views
  In order to ease opportunity management
  as a Sales Rep
  I should have a possibility to create Opportunity from related entity views

  Scenario: Sales Rep creates Opportunity for Account
    Given I login as "Johnconnor8" user
    And I go to Customers/Accounts
    And click View SkyNet in grid
    When I follow "More actions"
    And click "Create Opportunity"
    And I fill in "Opportunity name" with "First Invasion"
    And I save and close form
    Then I should see First Invasion with:
      | Opportunity Name | First Invasion |
      | Status           | Open           |
      | Account          | SkyNet         |
      | Probability      | 0%             |

  Scenario: Sales Rep creates Opportunity for Business Customers
    Given I go to Customers/Business Customers
    And click View BusSkyNet in grid
    When I follow "More actions"
    And click "Create Opportunity"
    And I fill in "Opportunity name" with "Third Invasion"
    And I save and close form
    Then I should see Third Invasion with:
      | Opportunity Name | Third Invasion |
      | Status           | Open           |
      | Account          | SkyNet         |
      | Probability      | 0%             |
