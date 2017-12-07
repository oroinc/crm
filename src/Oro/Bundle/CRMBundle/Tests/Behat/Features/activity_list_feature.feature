@fixture-OroCRMBundle:activities.yml
Feature: Activity list feature
  In order to have ability manage contact activity
  As OroCRM sales rep
  I need to view, filter, paginate activities in activity list

  Scenario: Filter activities by type
    Given I login as administrator
    Given I go to Customers/Contacts
    And click view Charlie in grid
    And there are 10 records in activity list
    When I check "Task" in Activity Type filter
    Then there are 2 records in activity list
    When I check "Email" in Activity Type filter
    Then there are 4 records in activity list
    When I check "Call" in Activity Type filter
    Then there are 6 records in activity list
    When I check "Note" in Activity Type filter
    Then there are 8 records in activity list
    When I check "Calendar event" in Activity Type filter
    Then there are 10 records in activity list

  Scenario: Paginate activity list
    Given the following note:
      | activityTargets   | createdAt                       | updatedAt                       |
      | [@contactCharlie] | <dateTimeBetween("now", "now")> | <dateTimeBetween("now", "now")> |
    And I reset Activity Type filter
    And I shouldn't see "Merry Christmas" email in activity list
    When go to older activities
    Then I should see "Merry Christmas" email in activity list

  Scenario: Filter activities by date range
    Given I go to newer activities
    And there are 10 records in activity list
    And I shouldn't see "Merry Christmas" email in activity list
    When I filter Date Range as between "2015-12-24" and "2015-12-26"
    Then I should see "Merry Christmas" email in activity list
    And there is one record in activity list
