# Magento integration is disabled in CRM-9202
@skip
@ticket-CRM-8748
@fixture-OroMagentoBundle:CustomerReportFixture.yml

Feature: Magento Customer report with Opportunity and Lead relations
  In order to manage reports
  As an Administrator
  I need to be able to use Opportunity and Lead relations in report for Magento Customer entity

  Scenario: Created report with Opportunity and Lead relations
    Given I login as administrator
    And I go to Reports & Segments/ Manage Custom Reports
    And I click "Create Report"
    When I fill form with:
      | Name        | Magento Customer Report |
      | Entity      | Magento Customer        |
      | Report Type | Table                   |
    And I add the following columns:
      | First name                    |
      | Last name                     |
      | Opportunity->Opportunity name |
      | Lead->Lead name               |
    And I save and close form
    Then I should see "Report saved" flash message
    And there are 10 records in grid
    And I should see following grid:
      | First name            | Last name             | Opportunity name | Lead name |
      | Magento Customer FN1  | Magento Customer LN1  | Opportunity 1    |           |
      | Magento Customer FN2  | Magento Customer LN2  | Opportunity 2    |           |
      | Magento Customer FN3  | Magento Customer LN3  | Opportunity 3    |           |
      | Magento Customer FN4  | Magento Customer LN4  | Opportunity 4    |           |
      | Magento Customer FN5  | Magento Customer LN5  | Opportunity 5    |           |
      | Magento Customer FN6  | Magento Customer LN6  |                  | Lead 6    |
      | Magento Customer FN7  | Magento Customer LN7  |                  | Lead 7    |
      | Magento Customer FN8  | Magento Customer LN8  |                  | Lead 8    |
      | Magento Customer FN9  | Magento Customer LN9  |                  | Lead 9    |
      | Magento Customer FN10 | Magento Customer LN10 |                  | Lead 10   |
