@regression
Feature: Mass Delete records with acl
	In order to control what records in what entities users can access
	As a Administrator
	I want give CRUD permissions to different user roles

Scenario: User can but now can't delete records
	Given I login as administrator
	And I have 5 Cases
	And I go to Activities/Cases
	And I keep in mind number of records in list
	And select few records
	And I set administrator permissions on Delete Cases to None
	When click "Delete" link from mass action dropdown
#	@todo uncomment when BAP-10919 bug will resolved
#	And confirm deletion
#	Then I should see "I don't have permissions" flash message
	Then no records were deleted

Scenario: User can't delete records
	Given I reload the page
	Then I shouldn't see Delete action

Scenario: User can delete only his records but view all
	Given administrator permissions on View Cases as Global and on Delete as User
	And there are two users with their own 7 Cases
	And keep in mind number of records in list
	And I reload the page
	And check all records in grid
	And click Delete mass action
	And confirm deletion
	Then I should see "5 entities have been deleted successfully" flash message

#@not-automated
#Scenario: Check limitation on Delete action
#	Given limit on delete action is set on 50 records
#	And I am on the Calls index page
#	And I have more than 50 Calls
#	When I select All records
#	And click Delete action
#	And confirm deletion
#	Then I should see message that I am able to delete only 50 records at once

Scenario: User can delete more records than can view
	Given administrator permissions on View Accounts as User and on Delete as Global
	And I have 3 Accounts
	And there are two users with their own 7 Accounts
	When I go to Customers/Accounts
	And I check all records in grid
	And click Delete mass action
	And confirm deletion
	Then I should see "17 entities have been deleted successfully" flash message
	And there is no records in grid
