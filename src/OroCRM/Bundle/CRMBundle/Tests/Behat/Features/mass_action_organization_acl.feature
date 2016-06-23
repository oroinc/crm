@fixture-mass_action_organization_acl.yml
Feature: Mass Delete records with acl organization
  In order to control what records in what entities users that  can access
  As a Administrator
  I want give CRUD permissions to different user roles

Background:
  Given I login as "admin" user with "admin" password

Scenario: Delete from System organization
  And I am logged in under System organization
  When I open the menu "Activities" and click "Contact Requests"
  And check all records in grid
  And click Delete mass action
  And confirm deletion
  Then I should see success message with number of records were deleted
  And all records should be deleted
