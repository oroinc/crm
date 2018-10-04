@fix-CRM-8741
@fixture-OroSalesBundle:OpportunityWithAdditionalCommentsFixture.yml
Feature: Additional comments in report fields
  As an administrator
  I want to have possibility to create custom Opportunity reports with field Additional comments

  Scenario: Report with group by Additional comments
    Given I login as administrator
    And I go to Reports & Segments/ Manage Custom Reports
    And I click "Create Report"
    And I fill "Report Form" with:
      | Name             | Grouped by additional comments |
      | Entity           | Opportunity                    |
      | Report Type      | Table                          |
      | Grouping Columns | Additional comments            |
    And I click "Add Grouping button"
    And I add the following columns:
      | Additional comments |
    When I save and close form
    Then I should see "Report saved" flash message
    And number of records should be 2

  Scenario: Report with functions for field Additional comments
    Given I go to Reports & Segments/ Manage Custom Reports
    And I click "Create Report"
    And I fill "Report Form" with:
      | Name             | Functions for additional comments |
      | Entity           | Opportunity                       |
      | Report Type      | Table                             |
      | Grouping Columns | Id                                |
    And I click "Add Grouping button"
    And I add the following columns:
      | Id                  | None  |
      | Additional comments | Count |
    When I save and close form
    Then I should see "Report saved" flash message
