Feature: Tie Opportunity probability to its status
  In order to improve sales forecasting
  As a Sales rep
  I want Opportunity probability to rely on its status

  Scenario: Feature background
    Given I login as administrator
    And "Sales Channel" is a channel with enabled Business Customer entity
    And CRM has next Opportunity Probabilities:
      | Status                     | Probability | Default |
      | Open                       | 5           |         |
      | Identification & Alignment | 20          |         |
      | Needs Analysis             | 10          | yes     |
      | Solution Development       | 60          |         |
      | Negotiation                | 75          |         |
      | Closed Won                 | 100         |         |
      | Closed Lost                | 0           |         |
    When I open Opportunity Create page
    Then Status field should has Needs Analysis value
    And Opportunity Probability must comply to Status:
      | Status                     | Probability |
      | Open                       | 5           |
      | Identification & Alignment | 20          |
      | Solution Development       | 60          |

  # todo: Should be created with required Customer Account association (could be filled with Account)
  Scenario: Add new Opportunity
    Given I fill form with:
      | Opportunity Name | Build new Death Star |
      | Status           | Needs Analysis       |
      | Account          | New Account          |
    When I save and close form
    Then should see Opportunity with:
      | Opportunity Name | Build new Death Star |
      | Status           | Needs Analysis       |
      | Probability      | 10%                  |

  Scenario: Inline Status edit
    Given I open Opportunity Index page
    When I edit Status as "Open"
    Then I should see Build new Death Star in grid with following data:
      | Status      | Open |
      | Probability | 5%   |

  Scenario: Inline Probability edit
    Given I edit Probability as "30"
    And I reload the page
    Then I should see Build new Death Star in grid with following data:
      | Status      | Open |
      | Probability | 30%  |

  Scenario: Change probability value
    Given I click Edit Build new Death Star in grid
    And I fill in "Probability" with "50"
    When I save and close form
    And should see Opportunity with:
      | Opportunity Name | Build new Death Star |
      | Status           | Open                 |
      | Probability      | 50%                  |

  Scenario: Change status not change previously changed probability
    Given I edit entity
    And I select "Negotiation" from "Status"
    When I save and close form
    And should see Opportunity with:
      | Opportunity Name | Build new Death Star |
      | Status           | Negotiation          |
      | Probability      | 50%                  |

