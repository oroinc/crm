@ticket-BAP-17189
@automatically-ticket-tagged
@fixture-OroSalesBundle:OpportunityFixture.yml
Feature: Create Report with Enum field
  In order to manage reports
  As administrator
  I need to be able to create report with Enum field

  Scenario: Created report with Enum field
    Given I login as administrator
    And I go to System/ Entities/ Entity Management
    And filter Name as is equal to "User"
    And I click view User in grid
    And click "Create field"
    And I fill form with:
      | Field Name   | CustomEnum  |
      | Storage Type | Table column  |
      | Type         | Select        |
    And I click "Continue"
    And set Options with:
      | Label   |
      | Option1 |
      | Option2 |
    And I save and close form
    Then I should see "Field saved" flash message
    When I click "Update schema"
    And I click "Yes, Proceed"
    Then I should see Schema updated flash message

    When go to System/User Management/Users
    And click Edit admin in grid
    And I fill form with:
      | CustomEnum | Option1 |
    And I save and close form
    Then I should see "User saved" flash message

    When I go to Reports & Segments / Manage Custom Reports
    And I click "Create Report"
    And I fill "Report Form" with:
      | Name        | Test Enum field in Report |
      | Entity      | Opportunity               |
      | Report Type | Table                     |
    And I add the following columns:
      | Id                |
      | Owner->CustomEnum |
    When I save and close form
    Then I should see "Report saved" flash message
    And there is one record in grid

    When I choose filter for CustomEnum as Is Any Of "Option1"
    Then there is one record in grid
