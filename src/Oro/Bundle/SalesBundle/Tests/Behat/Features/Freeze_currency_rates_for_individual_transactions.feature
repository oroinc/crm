@ticket-OEE-1208
@automatically-ticket-tagged
@not-automated
Feature: In order to ensure base currency amount for closed transactions is not adjusted according to going rates
  As an Administrator
  I want to ensure that currency exchange rates are frozen

  Background:
    Given the owner named John Doe
    And the following currencies:
    | Base  | Currency Name | Currency Code | Currency Symbol | Rate From | Rate To |
    | YES   | US Dollar     | USD           | $               | 1         | 1       |
    | NO    | Euro          | EUR           | €               | 2         | 2.5     |
    And workflow is disabled

Scenario: Ensure that for Opportunity with status "Open" Base budget amount and Base Close revenue fields
# are not editable
  Given I log in as Administrator
  And I go to Sales/ Opportunities
  And I press "Create Opportunity" button
  And "Create Opportunity" form is displayed
  And I fill out the form with the following data:
  | Owner     | Opportunity Name  | Channel         | Status  | Budget amount | Close revenue |
  | John Doe  | Josh Zuckerman    | Magento channel | Open    | 1000 EUR      | 2000 EUR      |
  And I should see the following:
  | Base budget amount  | Base Close revenue  |
  | $2,000.00           | $4,000.00           |
  When I press "Save and Close" button
  Then I should see the following on Opportunity view page:
    | Opportunity Name  | Status  | Budget amount | Base budget amount  | Close revenue | Base Close revenue  |
    | Josh Zuckerman    | Open    | €1,000.00     | $2,000.00           | €2,000.00     | $4,000.00           |
  But I press "Edit" button
  Then Base budget amount field should not be editable
  And Base Close revenue field should not be editable

  Scenario: Ensure that for Opportunity with status "Closed Won" Base budget amount and Base Close revenue fields
  # are editable
    Given I go to Sales/ Opportunities
    And I press "Create Opportunity" button
    And "Create Opportunity" form is displayed
    And I fill out the form with the following data:
      | Owner     | Opportunity Name  | Channel         | Status      | Budget amount | Close revenue |
      | John Doe  | James Stewart     | Magento channel | Closed Won  | 2000 EUR      | 3000 EUR      |
    And I should see the following:
      | Base budget amount  | Base Close revenue  |
      | $4,000.00           | $6,000.00           |
    When I press "Save and Close" button
    Then I should see the following on Opportunity view page:
      | Opportunity Name  | Status      | Budget amount | Base budget amount  | Close revenue | Base Close revenue  |
      | Josh Zuckerman    | Closed Won  | €2,000.00     | $4,000.00           | €3,000.00     | $6,000.00           |
    But I press "Edit" button
    Then Base budget amount field should be editable
    And Base Close revenue field should be editable

  Scenario: Ensure that for Opportunity with status "Closed Lost" Base budget amount and Base Close revenue fields
  # are editable
    Given I go to Sales/ Opportunities
    And I press "Create Opportunity" button
    And "Create Opportunity" form is displayed
    And I fill out the form with the following data:
      | Owner     | Opportunity Name  | Channel         | Status      | Budget amount | Close revenue |
      | John Doe  | Donna Reed        | Magento channel | Closed Lost  | 3000 EUR      | 5000 EUR     |
    And I should see the following:
      | Base budget amount  | Base Close revenue  |
      | $6,000.00           | $10,000.00          |
    When I press "Save and Close" button
    Then I should see the following on Opportunity view page:
      | Opportunity Name  | Status      | Budget amount | Base budget amount  | Close revenue | Base Close revenue  |
      | Donna Reed        | Closed Lost | €3,000.00     | $6,000.00           | €5,000.00     | $10,000.00          |
    But I press "Edit" button
    Then Base budget amount field should be editable
    And Base Close revenue field should be editable

    Scenario: Ensure that the user can edit "Base budget amount" and "Base Close revenue" fields for Opportunities in
      #statuses "Closed Won" and "Closed Lost"
      Given I go to Sales/ Opportunities
      And I press "Create Opportunity" button
      And "Create Opportunity" form is displayed
      And I fill out the form with the following data:
        | Owner     | Opportunity Name  | Channel         | Status      | Budget amount | Close revenue |
        | John Doe  | Lionel Barrymore  | Magento channel | Closed Won  | 3200 EUR      | 5400 EUR     |
      And I should see the following:
        | Base budget amount  | Base Close revenue  |
        | $6,400.00           | $10,800.00          |
      When I press "Save" button
      Then I should see the following on Opportunity view page:
        | Opportunity Name  | Status      | Budget amount | Base budget amount  | Close revenue | Base Close revenue  |
        | Lionel Barrymore  | Closed Won  | €3,000.00     | $6,400.00           | €5,000.00     | $10,800.00          |
      But I fill out the following:
        | Base budget amount  | Base close revenue  |
        | $5,300.00           | $9,500.00           |
      And I press "Save" button
      Then I should see the following on Opportunity view page:
        | Base budget amount  | Base close revenue  |
        | $5,300.00           | $9,500.00           |
      But I go to Sales/ Opportunities
      Then I should see on Opportunities grid the following:
        | Opportunity Name  | Status      | Budget amount | Base budget amount  | Close revenue | Base Close revenue  |
        | Lionel Barrymore  | Closed Won  | €3,000.00     | $5,300.00           | €5,000.00     | $9,500.00           |
      But I click on "Lionel Barrymore" opportunity on Opportunities grid
      And I click "Edit" button
      And I fill out the following:
      | Status      | Base budget amount  | Base Close revenue  |
      | Closed Lost | $6,700.00           | $10,860.00          |
      And I press "Save" button
      Then I should see the following on Opportunity view page:
        | Base budget amount  | Base close revenue  |
        | $6,700.00           | $10,860.00          |
      But I go to Sales/ Opportunities
      Then I should see on Opportunities grid the following:
        | Opportunity Name  | Status      | Budget amount | Base budget amount  | Close revenue | Base Close revenue  |
        | Lionel Barrymore  | Closed Won  | €3,000.00     | $6,700.00           | €5,000.00     | $10,860.00          |

    Scenario: Ensure that when the user changes currency rate, Base budget amount and Base Close revenue are not
    # recalculated for opportunities in statuses Closed Won and Closed Lost and are recalculated for opportunities in
    #status Open
    Given I go to System/ Configuration/ Currency
      And I fill out the following:
      | Base  | Currency name | Currency code | Currency Symbol | Rate from | Rate to |
      | No    | Euro          | EUR           | €               | 3         | 3.5     |
      And I press "Save Settings" button
      And I see "Configuration saved" flash message
      And I go to Sales/ Opportunities
      Then I should see the following on Opportunities grid:
      | Opportunity name  | Close Revenue  |  Base Close Revenue  | Budget Amount | Base Budget Amount  | Status      |
      | Josh Zuckerman    | €2,000.00      |  $6,000.00           | €1,000.00     | $3,000.00           | Open        |
      | James Stewart     | €3,000.00      |  $6,000.00           | €2,000.00     | $4,000.00           | Closed Won  |
      | Donna Reed        | €5,000.00      |  $10,000.00          | €3,000.00     | $6,000.00           | Closed Lost |
      | Lionel Barrymore  | €5,400.00      |  $10,860.00          | €3,200.00     | $6,700.00           | Closed Lost |

   Scenario: Ensure that Base Budget amount and base close currency for opportunities in status "Open" are recalculated
    Given I go press "Create Opportunity" button
     And I fill out the form with the following data:
     | Owner     | Opportunity Name | Channel         | Status | Budget amount | Close revenue |
     | John Doe  | Frank Faylen     | Magento channel | Open   | 100 EUR       | 200 EUR       |
     And I press "Save" button
     Then I should see
       | Opportunity Name | Status  | Budget amount | Base budget amount  | Close revenue | Base Close revenue  |
       | Frank Faylen     | Open    | €100.00       | $300.00             | €200.00       | $600.00             |
     But I enter "€200.00" into "Budget Amount" field
     Then I should see "$600.00" in "Base Budget Amount" field
     But I enter "€250.00" into "Close revenue" field
     Then I should see "$750.00" in "Base close revenue" field

  Scenario: Ensure that Base Budget amount and base close currency for opportunities in status "Closed Won" and "Closed
  # Lost" are not recalculated
    Given I select "Closed won" in the "Status" field
    And I press "Save"
    Then I should see
      | Opportunity Name | Status     | Budget amount | Base budget amount  | Close revenue | Base Close revenue  |
      | Frank Faylen     | Closed won | €200.00       | $600.00             | €250.00       | $750.00             |
    But I enter "€400.00" into "Budget Amount" field
    Then I should see "$600.00" in "Base Budget Amount" field
    But I enter "€550.00" into "Close revenue" field
    Then I should see "$750.00" in "Base close revenue" field
    But I select "Closed lost" in the "Status" field
    And I press "Save"
    Then I should see
      | Opportunity Name | Status       | Budget amount | Base budget amount  | Close revenue | Base Close revenue  |
      | Frank Faylen     | Closed lost  | €400.00       | $600.00             | €550.00       | $750.00             |
    But I enter "€200.00" into "Budget Amount" field
    Then I should see "$600.00" in "Base Budget Amount" field
    But I enter "€150.00" into "Close revenue" field
    Then I should see "$750.00" in "Base close revenue" field
    But I press "Save"
    Then I should see
      | Opportunity Name | Status       | Budget amount | Base budget amount  | Close revenue | Base Close revenue  |
      | Frank Faylen     | Closed lost  | €200.00       | $600.00             | €150.00       | $750.00             |

    # manual
    Scenario: Checking usecase with complete end-to-end scenario and widgets
      Given I go to System/ Configuration
      And I go to Currency
      And I fill out the following:
        | Base  | Currency name | Currency code | Currency Symbol | Rate from | Rate to |
        | No    | Euro          | EUR           | €               | 2         | 2.5     |
      And I press "Save Settings" button
      And I go to Sales/ Opportunities
      And I press "Create Opportunity" button
      And I see the Create Opportunity page
      And I fill out the form with the following:
      | Owner     | Opportunity Name  | Channel         | Status  | Budget Amount |
      | John Doe  | Ward Bond         | Magento channel | Open    | 100 EUR       |
      And I press "Save and Close" button
      And I go to Sales/ Opportunities
      And I select "Board" from the Kanban board/grid dropdown
      And I drag "Ward Bond" opportunity from "Open" to "Closed Won"
      And I should see "Ward Bond" opportunity in "Closed Won" column
      And I go to System/ Configuration
      And I go to Currency
      And I fill out the following:
        | Base  | Currency name | Currency code | Currency Symbol | Rate from | Rate to |
        | No    | Euro          | EUR           | €               | 3         | 3.5     |
      And I press "Save settings" button
      And I go to Sales/ Opportunities
      And I select Ward Bond opportunity on grid
      And I press "Edit" buttoneditable
      And I should see that base budget amount still equals 200B but is
      And I should see that both close revenue and base close revenue are editable
      And I fill out the following:
      | Budget amount | Close revenue |
      | 80 EUR        | 160 EUR       |
      And I press "Save and Close" button
      And I go to System/ Configuration
      And I go to Currency
      And I fill out the following:
        | Base  | Currency name | Currency code | Currency Symbol | Rate from | Rate to |
        | No    | Euro          | EUR           | €               | 3.5       | 3.5     |
      And I press "Save Settings" button
      And I go to Sales/ Opportunities
      Then I should see the following for Ward Bond opportunity on grid:
        | Budget amount | Close revenue |
        | 80 EUR        | 160 EUR       |
      And when I go to Dashboard
      And I observe Business Sales Channel Statistics widget
      Then I should see
      | New Opportunities amount  | Won Opportunities to date amount  |
      | $21,000.00                | $6,240.00                         |

  Scenario: Data Template for Opportunity
    Given I login as Administrator
    And I open Opportunity Index page
    And there is no records in grid
    When I download Data Template file
    Then I see the Opportunities import template with the following data:
      | Id                      |   |
      | Channel Name            |   |
      | Opportunity name        |   |
      | Expected close date     |   |
      | Probability             |   |
      | Budget Amount Value     |   |
      | Budget Amount Currency  |   |
      | Base Budget Amount      |   |
      | Customer need           |   |
      | Close revenue Value     |   |
      | Close revenue Currency  |   |
      | Base Close Revenue      |   |
      | Proposed solution       |   |
      | Additional comments     |   |
      | Status Id               |   |
      | Close reason Name       |   |
      | Account Customer name   |   |
      | Contact First name      |   |
      | Contact Last name       |   |
      | Lead Lead name          |   |
      | Owner Username          |   |
      | Organization Name       |   |
      | Tags                    |   |

    Scenario: "Base Budget Amount" and "Base Close Revenue" imported opportunity in Open status are not editable
      Given I fill Opportunities import template with the following data:
        | Id	                  |               |
        | Channel Name	          | Sales channel |
        | Opportunity name	      | Edward Asner  |
        | Expected close date	  |               |
        | Probability	          |               |
        | Budget Amount Value     | 100           |
        | Budget Amount Currency  | EUR           |
        | Base Budget Amount      | 109           |
        | Customer need           |               |
        | Close revenue Value     | 100           |
        | Close revenue Currency  | EUR           |
        | Base Close Revenue      | 109           |
        | Proposed solution       |               |
        | Additional comments     |               |
        | Status Id               | in_progress   |
        | Close reason Name       |               |
        | Account Customer name   |               |
        | Contact First name      |               |
        | Contact Last name       |               |
        | Lead Lead name          |               |
        | Owner Username          | admin         |
        | Organization Name       | Acme, Inc     |
        | Tags                    |               |
      When I import file
      And when I go to Sales/ Opportunities
      And when I click Edward Asner opportunity
      And when I click "Edit" button
      Then "Base Budget Amount" and "Base Close Revenue" should not be editable

      Scenario: "Base Budget Amount" and "Base Close Revenue" imported opportunity in Closed Won status are editable
        Given I fill Opportunities import template with the following data:
          | Id	                    |                   |
          | Channel Name            | Sales channel     |
          | Opportunity name        | Mary Steenburgen  |
          | Expected close date	    |                   |
          | Probability	            |                   |
          | Budget Amount Value     | 100               |
          | Budget Amount Currency  | EUR               |
          | Base Budget Amount      | 109               |
          | Customer need           |                   |
          | Close revenue Value     | 100               |
          | Close revenue Currency  | EUR               |
          | Base Close Revenue      | 109               |
          | Proposed solution       |                   |
          | Additional comments     |                   |
          | Status Id               | won               |
          | Close reason Name       |                   |
          | Account Customer name   |                   |
          | Contact First name      |                   |
          | Contact Last name       |                   |
          | Lead Lead name          |                   |
          | Owner Username          | admin             |
          | Organization Name       | Acme, Inc         |
          | Tags                    |                   |
        When I import file
        And when I go to Sales/ Opportunities
        And when I click Mary Steenburgen opportunity
        And when I click "Edit" button
        Then "Base Budget Amount" and "Base Close Revenue" should be editable

  Scenario: "Base Budget Amount" and "Base Close Revenue" imported opportunity in Closed Lost status are editable
    Given I fill Opportunities import template with the following data:
      | Id	                    |                   |
      | Channel Name            | Sales channel     |
      | Opportunity name        | Zooey Deschanel   |
      | Expected close date	    |                   |
      | Probability	            |                   |
      | Budget Amount Value     | 100               |
      | Budget Amount Currency  | EUR               |
      | Base Budget Amount      | 109               |
      | Customer need           |                   |
      | Close revenue Value     | 100               |
      | Close revenue Currency  | EUR               |
      | Base Close Revenue      | 109               |
      | Proposed solution       |                   |
      | Additional comments     |                   |
      | Status Id               | lost              |
      | Close reason Name       |                   |
      | Account Customer name   |                   |
      | Contact First name      |                   |
      | Contact Last name       |                   |
      | Lead Lead name          |                   |
      | Owner Username          | admin             |
      | Organization Name       | Acme, Inc         |
      | Tags                    |                   |
    When I import file
    And when I go to Sales/ Opportunities
    And when I click Mary Steenburgen opportunity
    And when I click "Edit" button
    Then "Base Budget Amount" and "Base Close Revenue" should be editable