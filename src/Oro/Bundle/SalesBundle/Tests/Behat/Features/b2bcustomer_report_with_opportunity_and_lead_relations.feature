@ticket-CRM-8748
@fixture-OroSalesBundle:B2bCustomerReportFixture.yml

Feature: B2bCustomer report with Opportunity and Lead relations
  In order to manage reports
  As an Administrator
  I need to be able to use Opportunity and Lead relations in report for B2bCustomer entity

  Scenario: Create integration
    Given I login as administrator
    And I go to System/ Channels
    And I click "Create Channel"
    When I fill form with:
      | Name         | Business Channel |
      | Channel type | Sales            |
    And save and close form
    Then I should see "Channel saved" flash message

  Scenario: Created report with Opportunity and Lead relations
    Given I go to Reports & Segments/ Manage Custom Reports
    And I click "Create Report"
    When I fill form with:
      | Name        | B2bCustomer Report |
      | Entity      | Business Customer  |
      | Report Type | Table              |
    And I add the following columns:
      | Customer name                 |
      | Opportunity->Opportunity name |
      | Lead->Lead name               |
    And I save and close form
    Then I should see "Report saved" flash message
    And there are 10 records in grid
    And I should see following grid:
      | Customer name  | Opportunity name | Lead name |
      | B2bCustomer 1  | Opportunity 1    |           |
      | B2bCustomer 2  | Opportunity 2    |           |
      | B2bCustomer 3  | Opportunity 3    |           |
      | B2bCustomer 4  | Opportunity 4    |           |
      | B2bCustomer 5  | Opportunity 5    |           |
      | B2bCustomer 6  |                  | Lead 6    |
      | B2bCustomer 7  |                  | Lead 7    |
      | B2bCustomer 8  |                  | Lead 8    |
      | B2bCustomer 9  |                  | Lead 9    |
      | B2bCustomer 10 |                  | Lead 10   |
