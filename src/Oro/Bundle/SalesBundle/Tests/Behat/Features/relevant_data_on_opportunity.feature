@non-automated
@draft
Feature: Display relevant data on Opportunity view
  In order to have 360-degree account view
  As Sales Rep
  I should have a possibility to see all opportunities related to current one

  Scenario: Feature background
    Given There is "Account" presented with following data:
      | Account Name | Description |
      | Disney Inc.  | Disney      |
    And there are "Opportunity" presented with following data:
      | Opportunity Name | Account     | Status                     | Probability (%) | Budget Amount |
      | Disney Logo      | Disney Inc. | Open                       | 10              | 500           |
      | Disney Toys      | Disney Inc. | Identification & Alignment | 25              | 10000         |
      | Disney Clothes   | Disney Inc. | Needs Analysis             | 50              | 50000         |
      | Disney Magnets   | Disney Inc. | Solution Development       | 70              | 5000          |
      | Disney Cups      | Disney Inc. | Negotiation                | 99              | 15000         |
    And following user exists:
      | First Name | Last Name | Username | Password  | Role      |
      | John       | Connor    | joconn   | Qwe123qwe | Sales Rep |

  Scenario: Observe the opportunity
    Given I login as "joconn" user
    And I go to Opportunities
    When I open "Disney Logo" opportunity
    Then I should see "Relevant opportunities" table
    And I should see following columns:
      | Opportunity name    |
      | Budget              |
      | Probability         |
      | Status              |
      | Expected close date |
      | Created at          |
    And I should see Opportunities in grid with following data:
      | Opportunity Name |
      | Disney Toys      |
      | Disney Clothes   |
      | Disney Magnets   |
      | Disney Cups      |
    And I should see 4 "Opportunity" elements
    And Elements sorted by "Expected close date" ascending

  Scenario: Navigate to related opportunity
    Given I am on "Disney Logo" view page
    When I click "Disney Toys"
    Then I should see "Disney Toys" view page

  Scenario: Hide Relevant opportunities
    Given I login as "Administrator" user
    And I go to Configuration/CRM/Sales Pipeline/Opportunity/Display settings
    And I uncheck "Display relevant opportunities"
    When I save setting
    Then I login as "joconn" user
    And I go to Opportunities
    And I open "Disney Logo" opportunity
    And I should not see "Relevant opportunities" table
