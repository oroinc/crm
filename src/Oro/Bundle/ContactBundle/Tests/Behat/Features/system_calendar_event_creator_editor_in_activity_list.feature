@ticket-BAP-16787
@fixture-OroContactBundle:SystemCalendarEventFixture.yml
@fixture-OroCalendarBundle:ActivityListFixture.yml

Feature: System calendar event creator editor in activity list
    Because system calendar events do not have creator or editor details
    As a User
    I should see only the system calendar event name in the activity list

    Scenario: Check displaying calendar event activity list item
        Given I login as administrator
        And I go to Customers/ Contacts
        And I click View Charlie Sheen in grid
        When I click on "Calendar Activity Item"
        Then I should see "System Calendar Event 1 Description"
        And I should not see "added by"
        And I should not see "updated by"

# Will eventually be replaced with the following, once BAP-16788 is implemented
#@ticket-BAP-16788
#Feature: System calendar event creator editor in activity list
#    In order to understand who created a calendar event
#    As an User
#    I should see the system calendar event name along with creator and editor info in the activity list
