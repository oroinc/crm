@not-automated
  #draft scenarios, will be updated and finalized when the feature is completed and merged to master by developers
Feature: Freeze currency rates for individual transactions
  In order to avoid data recalculation for locked transactions
  As an Administrator
  I want to check the base currency amount will not be adjusted according to the going rate

  Scenario: (GRID) Checking that when workflow is enabled and Opportunity is Closed Won, Base Budget Amount and Base Close
    Revenue are not recalculated on base currency change and are not editable
    Given I log in as Administrator
    And workflow is enabled
    And I create Opportunity 1
    And I close Opportunity 1 as Closed Won
    And I go to System/ Configuration
    And I click Currency
    And I change base currency
    When I go to Sales/ Opportunities
    Then I should see on Opportunities grid that base budget amount and base close revenue were not recalculated
    And base budget amount and base close revenue are not editable and are locked
    When I click Opportunity 1
    Then I should see that base budget amount and base close revenue were not recalculated
    And base budget amount and base close revenue are not editable and are locked
    When I click "Edit"
    Then I should see that base budget amount and base close revenue were not recalculated
    And base budget amount and base close revenue are not editable and are locked
    When I go to Dashboard
    Then I should see on widgets that base budget amount and base close revenue were not recalculated
    When I create report
    Then I should see that base budget amount and base close revenue were not recalculated
    When I go to Reports & Segments/ Manage Segments
    And I press "Create Segments"
    And I create custom segment
    Then I should see that base budget amount and base close revenue were not recalculated


  Scenario: (GRID) Checking that when workflow is enabled and Opportunity is Closed Lost, Base Budget Amount and Base Close
  Revenue are not recalculated on base currency change and are not editable
    Given I log in as Administrator
    And workflow is enabled
    And I create Opportunity 2
    And I close Opportunity 2 as Closed Lost
    And I go to System/ Configuration
    And I click Currency
    And I change base currency
    When I go to Sales/ Opportunities
    Then I should see on Opportunities grid that base budget amount and base close revenue were not recalculated
    And base budget amount and base close revenue are not editable and are locked
    When I click Opportunity 2
    Then I should see that base budget amount and base close revenue were not recalculated
    And base budget amount and base close revenue are not editable and are locked
    When I click "Edit"
    Then I should see that base budget amount and base close revenue were not recalculated
    And base budget amount and base close revenue are not editable and are locked
    When I go to Dashboard
    Then I should see on widgets that base budget amount and base close revenue were not recalculated
    When I create report
    Then I should see that base budget amount and base close revenue were not recalculated
    When I go to Reports & Segments/ Manage Segments
    And I press "Create Segments"
    And I create custom segment
    Then I should see that base budget amount and base close revenue were not recalculated

  Scenario: (GRID) Checking that when workflow is disabled and Opportunity is Closed Won, Base Budget Amount and Base
  Close  Revenue are not recalculated on base currency change and are editable
    Given I log in as Administrator
    And workflow is disabled
    And I create Opportunity 3
    And I close Opportunity 3 as Closed Won
    And I go to System/ Configuration
    And I click Currency
    And I change base currency
    When I go to Sales/ Opportunities
    Then I should see on Opportunities grid that base budget amount and base close revenue were not recalculated
    And base budget amount and base close revenue are editable and are not locked
    When I click Opportunity 3
    Then I should see that base budget amount and base close revenue were not recalculated
    And base budget amount and base close revenue are editable and are not locked
    When I click "Edit"
    Then I should see that base budget amount and base close revenue were not recalculated
    And base budget amount and base close revenue are editable and are not locked
    When I go to Dashboard
    Then I should see on widgets that base budget amount and base close revenue were not recalculated
    When I create report
    Then I should see that base budget amount and base close revenue were not recalculated
    When I go to Reports & Segments/ Manage Segments
    And I press "Create Segments"
    And I create custom segment
    Then I should see that base budget amount and base close revenue were not recalculated

  Scenario: (GRID) Checking that when workflow is disabled and Opportunity is Closed Lost, Base Budget Amount and Base
  Close  Revenue are not recalculated on base currency change and are editable
    Given I log in as Administrator
    And workflow is disabled
    And I create Opportunity 4
    And I close Opportunity 4 as Closed Lost
    And I go to System/ Configuration
    And I click Currency
    And I change base currency
    When I go to Sales/ Opportunities
    Then I should see on Opportunities grid that base budget amount and base close revenue were not recalculated
    And base budget amount and base close revenue are editable and are not locked
    When I click Opportunity 4
    Then I should see that base budget amount and base close revenue were not recalculated
    And base budget amount and base close revenue are editable and are not locked
    When I click "Edit"
    Then I should see that base budget amount and base close revenue were not recalculated
    And base budget amount and base close revenue are editable and are not locked
    When I go to Dashboard
    Then I should see on widgets that base budget amount and base close revenue were not recalculated
    When I create report
    Then I should see that base budget amount and base close revenue were not recalculated
    When I go to Reports & Segments/ Manage Segments
    And I press "Create Segments"
    And I create custom segment
    Then I should see that base budget amount and base close revenue were not recalculated

  Scenario: Editing Base Budget Amount and Base Close Revenue for Opportunity Closed Won when the workflow is disabled
    Given I log in as Administrator
    And workflow is enabled
    And I create Opportunity 5
    And I close Opportunity 5 as Closed Won
    And I go to System/ Configuration
    And I click Currency
    And I change base currency
    When I go to Sales/ Opportunities
    And I edit base budget amount and base close revenue for Opportunity 5 by inserting some data
    Then I should see updated values for base budget amount and base close revenue in Opportunity grid, in Opportunity
      view, when editing opportunity, on widgets, reports and segments

  Scenario: Editing Base Budget Amount and Base Close Revenue for Opportunity Closed Lost when the workflow is disabled
    Given I log in as Administrator
    And workflow is enabled
    And I create Opportunity 6
    And I close Opportunity 6 as Closed Lost
    And I go to System/ Configuration
    And I click Currency
    And I change base currency
    When I go to Sales/ Opportunities
    And I edit base budget amount and base close revenue for Opportunity 6 by inserting some data
    Then I should see updated values for base budget amount and base close revenue in Opportunity grid, in Opportunity
  view, when editing opportunity, on widgets, reports and segments

  Scenario: (KANBAN) Checking that when workflow is enabled and Opportunity is Closed Won, Base Budget Amount and Base Close
  Revenue are not recalculated on base currency change and are not editable
    Given I log in as Administrator
    And workflow is enabled
    And I create Opportunity 7
    And I close Opportunity 7 as Closed Won via KANBAN
    And I go to System/ Configuration
    And I click Currency
    And I change base currency
    When I go back to Sales/ Opportunities
    Then I should see on Opportunities grid that base budget amount and base close revenue were not recalculated
    And base budget amount and base close revenue are not editable and are locked
    When I click Opportunity 7
    Then I should see that base budget amount and base close revenue were not recalculated
    And base budget amount and base close revenue are not editable and are locked
    When I click "Edit"
    Then I should see that base budget amount and base close revenue were not recalculated
    And base budget amount and base close revenue are not editable and are locked
    When I go to Dashboard
    Then I should see on widgets that base budget amount and base close revenue were not recalculated
    When I create report
    Then I should see that base budget amount and base close revenue were not recalculated
    When I go to Reports & Segments/ Manage Segments
    And I press "Create Segments"
    And I create custom segment
    Then I should see that base budget amount and base close revenue were not recalculated


  Scenario: (KANBAN) Checking that when workflow is enabled and Opportunity is Closed Lost, Base Budget Amount and Base Close
  Revenue are not recalculated on base currency change and are not editable
    Given I log in as Administrator
    And workflow is enabled
    And I create Opportunity 8
    And I close Opportunity 8 as Closed Lost via Kanban
    And I go to System/ Configuration
    And I click Currency
    And I change base currency
    When I go to Sales/ Opportunities
    Then I should see on Opportunities grid that base budget amount and base close revenue were not recalculated
    And base budget amount and base close revenue are not editable and are locked
    When I click Opportunity 8
    Then I should see that base budget amount and base close revenue were not recalculated
    And base budget amount and base close revenue are not editable and are locked
    When I click "Edit"
    Then I should see that base budget amount and base close revenue were not recalculated
    And base budget amount and base close revenue are not editable and are locked
    When I go to Dashboard
    Then I should see on widgets that base budget amount and base close revenue were not recalculated
    When I create report
    Then I should see that base budget amount and base close revenue were not recalculated
    When I go to Reports & Segments/ Manage Segments
    And I press "Create Segments"
    And I create custom segment
    Then I should see that base budget amount and base close revenue were not recalculated

  Scenario: (KANBAN) Checking that when workflow is disabled and Opportunity is Closed Won, Base Budget Amount and Base
  Close  Revenue are not recalculated on base currency change and are editable
    Given I log in as Administrator
    And workflow is disabled
    And I create Opportunity 9
    And I close Opportunity 9 as Closed Won via KANBAN
    And I go to System/ Configuration
    And I click Currency
    And I change base currency
    When I go to Sales/ Opportunities
    Then I should see on Opportunities grid that base budget amount and base close revenue were not recalculated
    And base budget amount and base close revenue are editable and are not locked
    When I click Opportunity 9
    Then I should see that base budget amount and base close revenue were not recalculated
    And base budget amount and base close revenue are editable and are not locked
    When I click "Edit"
    Then I should see that base budget amount and base close revenue were not recalculated
    And base budget amount and base close revenue are editable and are not locked
    When I go to Dashboard
    Then I should see on widgets that base budget amount and base close revenue were not recalculated
    When I create report
    Then I should see that base budget amount and base close revenue were not recalculated
    When I go to Reports & Segments/ Manage Segments
    And I press "Create Segments"
    And I create custom segment
    Then I should see that base budget amount and base close revenue were not recalculated

  Scenario: (KANBAN) Checking that when workflow is disabled and Opportunity is Closed Lost, Base Budget Amount and Base
  Close  Revenue are not recalculated on base currency change and are editable
    Given I log in as Administrator
    And workflow is disabled
    And I create Opportunity 10
    And I close Opportunity 10 as Closed Lost
    And I go to System/ Configuration
    And I click Currency
    And I change base currency
    When I go to Sales/ Opportunities
    Then I should see on Opportunities grid that base budget amount and base close revenue were not recalculated
    And base budget amount and base close revenue are editable and are not locked
    When I click Opportunity 10
    Then I should see that base budget amount and base close revenue were not recalculated
    And base budget amount and base close revenue are editable and are not locked
    When I click "Edit"
    Then I should see that base budget amount and base close revenue were not recalculated
    And base budget amount and base close revenue are editable and are not locked
    When I go to Dashboard
    Then I should see on widgets that base budget amount and base close revenue were not recalculated
    When I create report
    Then I should see that base budget amount and base close revenue were not recalculated
    When I go to Reports & Segments/ Manage Segments
    And I press "Create Segments"
    And I create custom segment
    Then I should see that base budget amount and base close revenue were not recalculated

  Scenario: Checking that when Opportunity was Closed Won/Lost and then was returned back to previous statuses,
    Base Budget Amount and Base Close revenue are recalculated on changing base currency
    Given I log in as Administrator
    And I create Opportunity 11
    And I close Opportunity 11 as <Opportunity Status>
    | Opportunity Status|
    | Closed Won        |
    | Closed Lost       |
    And I change base currency
    Then values for Base Budget Amount and Base Close revenue are not recalculated on
    Opportunity grid, Opportunity view, Opportunity Edit, Dashboard widgets, Reports and Segments
    When I edit Opportunity by assigning back <Opportunity RT Status>:
    | Opportunity RT Status       |
    | Open                        |
    | Identification & Alignment  |
    | Needs Analysis              |
    | Solution Development        |
    | Negotiation                 |
    And I change base currency
    Then values for Base Budget Amount and Base Close revenue are recalculated on
  Opportunity grid, Opportunity view, Opportunity Edit, Dashboard widgets, Reports and Segments


