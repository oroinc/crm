@fixture-activities.yml
Feature: Activity list feature
  In order to have ability manage contact activity
  As OroCRM sales rep
  I need to view, filter, paginate activities in activity list

Background:
  Given I login as "admin" user with "admin" password

Scenario: Paginate activity list
  Given I go to Customers/Contacts
  When click view Charlie in grid
  Then there are 50 records in activity list
  And I shouldn't see "Merry Christmas" email in activity list
  When go to 5 page of activity list
  Then I should see "Merry Christmas" email in activity list

Scenario: Filter activities by date range
  Given I go to Customers/Contacts
  And click view Charlie in grid
  And I shouldn't see "Merry Christmas" email in activity list
  When I filter Date Range as between "2015-12-24" and "2015-12-26"
  Then I should see "Merry Christmas" email in activity list

Scenario: Filter activities by type
  Given I go to Customers/Contacts
  And click view Charlie in grid
  And there are 50 records in activity list
  When I check "Task" in Activity Type filter
  Then there are 10 records in activity list
  When I check "Email" in Activity Type filter
  Then there are 20 records in activity list
  When I check "Call" in Activity Type filter
  Then there are 30 records in activity list
  When I check "Note" in Activity Type filter
  Then there are 40 records in activity list
  When I check "Calendar event" in Activity Type filter
  Then there are 50 records in activity list
