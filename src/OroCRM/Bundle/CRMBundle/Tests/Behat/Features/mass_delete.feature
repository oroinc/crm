@fixture-mass_action.yml
Feature: Mass Delete records
	In order to decrease time needed to delete several records at once
	As a crm administrator
	I want to use mass delete functionality

Background:
	Given I login as "admin" user with "admin" password

Scenario: Update Sales Channel
	Given I go to System/Channels
	And click edit Sales Channel in grid
	When I press "Save and Close"
	Then I should see "Channel saved" flash message

Scenario Outline: No records to delete selected
	When go to <menu>
	And I don't select any record from Grid
	And I click "Delete" link from mass action dropdown
	Then I should see "Please select items to delete." flash message

	Examples:
	| menu               |
	| Customers/Contacts |
	| Activities/Tasks   |

Scenario: Delete few manually selected records
	Given go to Customers/ Accounts
	And I keep in mind number of records in list
	When I check first 2 records in grid
	And I click "Delete" link from mass action dropdown
	And confirm deletion
	Then I should see "2 entities were deleted" flash message
	And the number of records decreased by 2

Scenario: Cancel Delete records
	Given I go to Customers/Business Customers
	And I keep in mind number of records in list
	And I check first 2 records in grid
	When I click "Delete" link from mass action dropdown
	And cancel deletion
	And the number of records remained the same

Scenario: Select and delete All Visible records
	Given I go to Customers/Contacts
	And I keep in mind number of records in list
	And I select 10 from per page list dropdown
	When I check All Visible records in grid
	And I click "Delete" link from mass action dropdown
	And confirm deletion
	Then the number of records decreased by 10

Scenario: Select and delete All records
	Given I go to Customers/Contacts
	When I check all records in grid
	And I click "Delete" link from mass action dropdown
	And confirm deletion
	Then there is no records in grid

Scenario: Uncheck few records
	Given I go to Activities/Tasks
	And I keep in mind number of records in list
	And I select 10 from per page list dropdown
	When I check All Visible records in grid
	And I uncheck first 2 records in grid
	And I click "Delete" link from mass action dropdown
	And confirm deletion
	Then the number of records decreased by 8

#@skip
# ToDo: uncomment when BAP-10673 will completed
#Scenario Outline: Delete different number of records
#	Given I have 6000 of Accounts in the system
#	And I am on the Accounts page
#	When I select <number> records
#	And click Delete button from Grid
#	And confirm deletion
#	Then selected records should be deleted
#
#	Examples:
#	| number |
#	| 10     |
#	| 199    |
#	| 1000   |
#	| 5000   |
