@regression
@fixture-OroContactBundle:contacts.yml
Feature: Contacts grid
  In order to have ability work with contacts
  As administrator
  I need to have grid with filters, sorters, pagination features

Scenario: Select records per page
  Given I login as administrator
  And I go to Customers/Contacts
  And number of records should be 30
  And number of pages should be 2
  When I select 10 records per page
  Then number of pages should be 3
  When I select 50 records per page
  Then number of pages should be 1

Scenario: Page navigation
  Given I select 10 records per page
  When I press next page button
  Then number of page should be 2
  When I fill 4 in page number input
  Then number of page should be 3

Scenario: Sorting grid by created at
  Given I select 50 records per page
  When sort grid by Created at
  Then Created At in first row must be lower then in second row
  But when I sort grid by Created At again
  Then Created At in first row must be greater then in second row

Scenario: Sorting grid by first name
  Given I select 50 records per page
  When sort grid by First Name
  Then Aadi AABERG must be first record
  But when I sort grid by First Name again
  Then Zyta Zywiec must be first record

Scenario: Filter grid by plain text
  Given I select 50 records per page
  And number of records should be 30
  When I filter First Name as Contains "Aadi"
  And filter Last Name as Contains "AABERG"
  Then number of records should be 1
  And I reset First Name filter
  And I reset Last Name filter

Scenario: Filter grid by date time range
  Given I select 50 records per page
  And number of records should be 30
  When I filter Created At as between "25 Jun 2015" and "30 Jun 2015"
  Then number of records should be 1
  And Zyta Zywiec must be first record
  But when I filter Created At as not between "25 Jun 2015" and "30 Jun 2015"
  Then number of records should be 29
  And I reset Created At filter

Scenario: Mass delete action while filter is set
  Given I select 50 records per page
  And I filter First Name as Contains "John"
  And sort grid by Email
  When I check first 2 records in grid
  And I click "Delete" link from mass action dropdown
  And I click "Yes, Delete"
  Then I should see "2 entities have been deleted successfully" flash message
  And I reset First Name filter
  And number of records should be 28
