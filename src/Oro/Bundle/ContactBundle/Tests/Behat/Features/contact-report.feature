@ticket-BAP-14903
@fixture-OroContactBundle:contacts.yml
@fixture-OroContactBundle:tags.yml
Feature: Contacts reports
  In order to have ability work with contacts
  As administrator
  I need to have create reports

  Scenario: Create report with contacts and tags
    Given I login as administrator
    And I go to Reports & Segments/ Manage Custom Reports
    And I click "Create Report"
    And I fill "Report Form" with:
      | Name        | Contacts |
      | Entity      | Contact  |
      | Report Type | Table    |
    And I add the following columns:
      | First name |
      | Last name  |
      | Tags->Name |
    When I save and close form
    Then I should see "Report saved" flash message
    And number of pages should be 2
    And number of records should be 38
