@ticket-CRM-9055
@ticket-BAP-18600
@fixture-OroSalesBundle:OpportunityFixture.yml
@fixture-OroSalesBundle:OpportunityTagsFixture.yml
Feature: Create Report with Dictionary field
  In order to manage reports
  As administrator
  I need to be able to create report with Dictionary field

  Scenario: Created report with Dictionary field and "is not empty" filter
    Given I login as administrator
    And go to Sales/Opportunities
    And click Edit "Opportunity 1" in grid
    And I fill form with:
      | Close Reason | Cancelled |
    And I save and close form
    And I should see "Opportunity saved" flash message
    When I go to Reports & Segments / Manage Custom Reports
    And I click "Create Report"
    And I fill "Report Form" with:
      | Name        | Test Dictionary field in Report (is not empty) |
      | Entity      | Opportunity                                    |
      | Report Type | Table                                          |
    And I add the following columns:
      | Opportunity name | None |     |
      | Close reason     | None |     |
      | Tags->Name       | None | Tag |
    And I add the following filters:
      | Field Condition | Tags         | is not empty |
      | Field Condition | Close reason | is not empty |
    And I save and close form
    Then I should see "Report saved" flash message
    And number of records should be 2
    And I sort grid by "Tag"
    And I should see following grid:
      | Opportunity name | Close reason | Tag  |
      | Opportunity 1    | Cancelled    | tag1 |
      | Opportunity 1    | Cancelled    | tag2 |

  Scenario: Created report with Dictionary field and "is empty tags" filter
    Given I go to Reports & Segments / Manage Custom Reports
    When I click "Create Report"
    And I fill "Report Form" with:
      | Name        | Test Dictionary field in Report (is empty tags) |
      | Entity      | Opportunity                                     |
      | Report Type | Table                                           |
    And I add the following columns:
      | Opportunity name | None |     |
      | Close reason     | None |     |
      | Tags->Name       | None | Tag |
    And I add the following filters:
      | Field Condition | Tags | is empty |
    And I save and close form
    Then I should see "Report saved" flash message
    And number of records should be 0

  Scenario: Created report with Dictionary field and "is empty close reason" filter
    Given I go to Reports & Segments / Manage Custom Reports
    When I click "Create Report"
    And I fill "Report Form" with:
      | Name        | Test Dictionary field in Report (is empty close reason) |
      | Entity      | Opportunity                                             |
      | Report Type | Table                                                   |
    And I add the following columns:
      | Opportunity name | None |     |
      | Close reason     | None |     |
      | Tags->Name       | None | Tag |
    And I add the following filters:
      | Field Condition | Close reason | is empty |
    And I save and close form
    Then I should see "Report saved" flash message
    And number of records should be 0
