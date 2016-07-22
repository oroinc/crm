Feature: Calendar event activity
  In order to have schedule for every day
  As OroCRM sales rep
  I need to create, edit, view and delete calendar events

Background:
  Given I login as "admin" user with "admin" password

Scenario: Add calendar event
  Given there are following users:
    | firstName | lastName | email              | username | organization  | organizations   | owner          | businessUnits    |
    | Theresa   | Peters   | theresa@peters.com | theresa  | @organization | [@organization] | @business_unit | [@business_unit] |
    | Jeremy    | Zimmer   | jeremy@zimmer.com  | jeremy   | @organization | [@organization] | @business_unit | [@business_unit] |
  And the following contact:
    | firstName | lastName | email             |
    | Charlie   | Sheen    | charlie@sheen.com |
  And I go to Customers/Contacts
  And click view Charlie in grid
  And follow "More actions"
  And follow "Add Event"
  And I fill form with:
    | Title       | Contract sign in            |
    | Description | Be sure that you have a pen |
    | Start       | 2017-08-24 15:00:00         |
    | End         | 2017-08-24 17:00:00         |
    | Guests      | [Theresa, Jeremy]           |
  And set Reminders with:
    | Method        | Interval unit | Interval number |
    | Email         | days          | 1               |
    | Flash message | minutes       | 30              |
  When I press "Save"
  Then I should see "Calendar event saved" flash message
  And should see "Contract sign in" calendar event in activity list

Scenario: View Calendar event in User view page
  Given I go to Customers/Contacts
  And click view Charlie in grid
  When I collapse "Contract sign in" in activity list
  Then I should see calendar event with:
    | Title         | Contract sign in            |
    | Description   | Be sure that you have a pen |
    | All-day event | No                          |

Scenario: View Calendar event in Calendar event view page
  Given I go to Customers/Contacts
  And click view Charlie in grid
  When I click "View event" on "Contract sign in" in activity list
  Then the url should match "/calendar/event/view/\d+"
  And I should see calendar event with:
    | Title         | Contract sign in            |
    | Description   | Be sure that you have a pen |
    | All-day event | No                          |

Scenario: Edit Calendar event activity
  Given I go to Customers/Contacts
  And click view Charlie in grid
  When I click "Update calendar event" on "Contract sign in" in activity list
  And I fill form with:
    | Title       | Discuss about contract        |
    | Description | Ask all the necessary details |
    | Start       | 2017-09-01 12:00:00           |
    | End         | 2017-09-01 14:00:00           |
    | Guests      | [Jeremy]                      |
  And set Reminders with:
    | Method        | Interval unit | Interval number |
    | Email         | weeks         | 3               |
    | Flash message | hours         | 1               |
  And press "Save"
  Then I should see "Calendar event saved" flash message
  When I collapse "Discuss about contract" in activity list
  And I should see calendar event with:
    | Title         | Discuss about contract        |
    | Description   | Ask all the necessary details |
    | All-day event | No                            |

Scenario: Delete Calendar event activity
  Given I go to Customers/Contacts
  And click view Charlie in grid
  When I click "Delete calendar event" on "Discuss about contract" in activity list
  And confirm deletion
  Then I should see "Activity item deleted" flash message
  And there is no records in activity list
