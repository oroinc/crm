@ticket-CRM-7754
@automatically-ticket-tagged
@not-automated
Feature: Break Business Sales Channel statistics widget into two
  In order to check the performance of any lead/opportunity pipeline
  As a Sales rep
  I want to use advanced filters in Business Sales Channel statistics widget

  Scenario: Feature background
    Given there is following Account:
      | Account Name |
      | AllEurope    |
    And there is following Segment:
      | Segment name     | Entity      | Column Name      | Condition                        |
      | Leads Europe     | Lead        | Lead Name        | Lead Name contains Europe        |
      | Oppo Europe      | Opportunity | Opportunity Name | Opportunity Name contains Chance |
    And there are following Territory:
      | Name         | Priority | Segment      |
      | L Europe     | 9        | Leads Europe |
      | O Europe     | 9        | Oppo Europe  |
      | L Europe Old | 9        | Leads Europe |
      | O Europe Old | 9        | Oppo Europe  |
    And there are following Lead:
      | Lead Name           | Lead Status  | Territory    |
      | Europe Lead 1       | Open         | L Europe     |
      | Europe Lead 2       | Qualified    | L Europe     |
      | Europe Lead 3 Old   | Disqualified | L Europe Old |
      | Europe Lead 4       | Open         | L Europe     |
      | Europe Lead 5 Old   | Qualified    | L Europe Old |
      | Europe Lead 6       | Disqualified | L Europe     |
      | Europe Lead 7 Old   | Qualified    | L Europe Old |
      | Europe Lead 8       | Disqualified | L Europe     |
      | Europe Lead 9 Old   | Qualified    | L Europe Old |
    And there are following Opportunity:
      | Opportunity Name | Account   | Status                       | Budget Amount | Close revenue | Territory    |
      | EuroChance 1     | AllEurope | Open                         | 12000         | 10000         | O Europe     |
      | EuroChance 2 Old | AllEurope | Identification and Alignment | 1000          | 3000          | O Europe Old |
      | EuroChance 3     | AllEurope | Needs Analysis               | 10000         | 8000          | O Europe     |
      | EuroChance 4 Old | AllEurope | Solution Development         | 17000         | 11000         | O Europe Old |
      | EuroChance 5     | AllEurope | Closed as Won                | 2000          | 7000          | O Europe     |
      | EuroChance 6     | AllEurope | Closed as Lost               | 3000          | 4000          | O Europe     |
      | EuroChance 7 Old | AllEurope | Closed as Won                | 5000          | 1000          | O Europe Old |
      | EuroChance 8     | AllEurope | Negotiation                  | 22000         | 20000         | O Europe     |
      | EuroChance 9 Old | AllEurope | Closed as Won                | 32000         | 30000         | O Europe Old |
      | EuroChance 10    | AllEurope | Closed as Won                | 20000         | 19000         | O Europe     |
    And there are following User:
      | Username | Roles         |
      | OroAdmin | Administrator |
      | SalesMan | Sales Rep     |
    And "Opportunity Management Flow" workflow is activated

  Scenario: SalesMan adds new Leads widgets to the dashboard
    Given I login as "SalesMan" user
    And I click "Add Widget"
    And I type "Lead" in "Keyword field"
    When I click "Add" opposite to "Lead Statistics"
    And I close ui dialog
    Then I should see "Lead Statistics" widget on the dashboard
    And I should see the following data:
      | New Leads       | 9 |
      | Qualified Leads | 4 |
      | Converted Leads | 0 |
      | Open Leads      | 2 |

  Scenario: SalesMan adds new Opportunities widgets to the dashboard
    Given I click "Add Widget"
    And I type "Oppo" in "Keyword field"
    When I click "Add" opposite to "Opportunity Statistics"
    And I close ui dialog
    Then I should see "Opportunity Statistics" widget on the dashboard
    And I should see the following data:
      | New Opportunities count                 | 10     |
      | New Opportunities budget amount         | 124000 |
      | Won Opportunities to date count         | 4      |
      | Won Opportunities to date budget amount | 57000  |


  Scenario: SalesMan converts Lead to Opportunity
    Given I go to Sales/Leads
    And I open Europe Lead 4 page
    And I click "Convert to Opportunity"
    And I fill in the following:
      | Opportunity Name | Europe Opportunity 4 |
      | Account          | AllEurope            |
      | Status           | Open                 |
      | Budget Amount    | 10000                |
      | Territory        | O Europe             |
    And I save setting
    When I go to Dashboard
    Then I should see the following data:
      | New Leads 							    | 8      |
      | Qualified Leads 					    | 4      |
      | Converted Leads 					    | 1      |
      | Open Leads 							    | 1      |
      | New Opportunities count 			    | 11     |
      | New Opportunities budget amount 	    | 134000 |
      | Won Opportunities to date count 		| 4      |
      | Won Opportunities to date budget amount | 57000  |

  Scenario: SalesMan closes as one newly created Opportunity
    Given I go to Sales/Opportunities
    And I open Europe Opportunity 4 page
    When I click "Close as Won" button
    And I fill in "Close Revenue" with "20000"
    And I submit form
    And I go to Dashboard
    Then I should see the following data:
      | New Opportunities count                 | 11     |
      | New Opportunities budget amount         | 134000 |
      | Won Opportunities to date count         | 5      |
      | Won Opportunities to date budget amount | 77000  |
