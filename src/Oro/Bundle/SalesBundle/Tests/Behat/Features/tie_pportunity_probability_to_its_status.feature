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
    When I open Opportunity Create page
    Then Status field should has Needs Analysis value
    And Opportunity Probability must comply to Status:
      | Status                     | Probability |
      | Open                       | 5           |
      | Identification & Alignment | 20          |
      | Solution Development       | 60          |

  Scenario: Add new Opportunity with empty probability
    Given I fill form with:
      | Opportunity Name | Opportunity with empty Probability |
      | Account          | Darth Vader                        |
      | Status           | Negotiation                        |
      | Probability      |                                    |
    When I save and close form
    Then I should see "Opportunity saved" flash message
    And should see Opportunity with:
      | Opportunity Name | Opportunity with empty Probability |
      | Status           | Negotiation                        |
      | Probability      | N/A                                |

  Scenario: Add new Opportunity
    Given I open Opportunity Create page
    And I fill form with:
      | Opportunity Name | Build new Death Star |
      | Account          | Darth Vader          |
      | Status           | Needs Analysis       |
    When I save and close form
    And should see Opportunity with:
      | Opportunity Name | Build new Death Star |
      | Status           | Needs Analysis       |
      | Probability      | 10%                  |

  Scenario: Change probability value
    Given I edit entity
    And I fill in "Probability" with "50"
    When I save and close form
    And should see Opportunity with:
      | Opportunity Name | Build new Death Star |
      | Status           | Needs Analysis       |
      | Probability      | 50%                  |

  Scenario: Change status not change previously changed probability
    Given I edit entity
    And I select "Negotiation" from "Status"
    When I save and close form
    And should see Opportunity with:
      | Opportunity Name | Build new Death Star |
      | Status           | Negotiation          |
      | Probability      | 50%                  |

  Scenario: Inline Status edit
    Given I open Opportunity Index page
    When I edit "Build new Death Star" Status as "Open"
    Then I should see Build new Death Star in grid with following data:
      | Status      | Open |
      | Probability | 5%   |
