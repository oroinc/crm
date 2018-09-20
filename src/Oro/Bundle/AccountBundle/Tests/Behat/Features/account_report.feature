@regression
@fix-BAP-14500
Feature: Account report
  In order to see Account related information in report
  As a administrator
  I want to have possibility to create custom Account reports

  Scenario: Create new Report
    Given I login as administrator
    And I go to Reports & Segments/ Manage Custom Reports
    And I click "Create Report"
    And I fill "Report Form" with:
      | Name        | Account report |
      | Entity      | Account        |
      | Report Type | Table          |
    And I add the following columns:
      | Id                   |
      | Lifetime sales value |
    When I save form
    Then I should see "Report saved" flash message
