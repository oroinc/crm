Feature: Tie Opportunity probability to its status
  In order to improve sales forecasting
  As a Sales rep
  I want Opportunity probability to rely on its status

  Scenario: Background:
    Given I login as administrator
    And "Sales Channel" is a channel with enabled Opportunity, Lead, Business Customer entities
    And CRM has next Opportunity Probabilities:
      | Status                     | Probability | Default |
      | Open                       | 5           |         |
      | Identification & Alignment | 20          |         |
      | Needs Analysis             | 10          | yes     |
      | Solution Development       | 60          |         |
      | Negotiation                | 75          |         |
      | Closed Won                 | 100         |         |
      | Closed Lost                | 0           |         |
    When I open Opportunity creation page
    Then Status field should have Needs Analysis value
    And Opportunity Probability must comply to Status:
      | Status                     | Probability |
      | Open                       | 5           |
      | Identification & Alignment | 20          |
      | Solution Development       | 60          |

  Scenario: Add new Opportunity
    Given I fill form with:
      | Opportunity Name | Build new Death Star |
      | Account          | Darth Vader          |
      | Status           | Needs Analysis       |
    When I save and close form
    Then I should see "Opportunity saved" flash message
    And should see Opportunity with:
      | Opportunity Name | Build new Death Star |
      | Status           | Needs Analysis       |
      | Probability      | 10%                  |

  Scenario: Add new Opportunity with empty probability
    Given I edit entity
    And I fill in "Probability" with "50"
    When I save and close form
    Then I should see "Opportunity saved" flash message
    And should see Opportunity with:
      | Opportunity Name | Build new Death Star |
      | Status           | Needs Analysis       |
      | Probability      | 50%                  |
