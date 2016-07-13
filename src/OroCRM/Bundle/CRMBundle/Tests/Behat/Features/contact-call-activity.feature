Feature: Contact call activity
  In order to have view all phone collaboration with contact
  As OroCRM sales rep
  I need to log calls, edit, create and delete it

Background:
  Given I login as "admin" user with "admin" password

Scenario: Log Call from contact view page
  Given the following contact:
    | firstName | lastName | email             |
    | Charlie   | Sheen    | charlie@sheen.com |
  And the following users:
    | firstName | lastName | email              | username | organization  | organizations   | owner          | businessUnits    |
    | Theresa   | Peters   | theresa@peters.com | theresa  | @organization | [@organization] | @business_unit | [@business_unit] |
  And I go to Customers/Contacts
  And click view Charlie in grid
  And follow "More actions"
  And follow "Log call"
  And fill form with:
    | Subject             | Proposed Charlie to star in new film |
    | Additional comments | Charlie was in a good mood           |
    | Call date & time    | 2017-08-24                           |
    | Phone number        | (310) 475-0859                       |
    | Duration            | 00:05:30                             |
  When I press "Log call"
  Then I should see "Call saved" flash message
  And should see "Proposed Charlie to star in new film" call in activity list

Scenario: View Call in activity list
  Given I go to Customers/Contacts
  And click view Charlie in grid
  When I collapse "Proposed Charlie to star in new film" in activity list
  Then I should see call with:
    | Subject             | Proposed Charlie to star in new film |
    | Additional comments | Charlie was in a good mood           |
    | Call date & time    | 2017-08-24                           |
    | Phone number        | (310) 475-0859                       |
    | Direction           | Outgoing                             |
    | Duration            | 5:30                                 |
  And I should see Charlie in Contexts

Scenario: View Call in view call page
  Given I go to Customers/Contacts
  And click view Charlie in grid
  When I click "View call log" on "Proposed Charlie to star in new film" in activity list
  Then the url should match "/call/view/\d+"
  And I should see call with:
    | Subject             | Proposed Charlie to star in new film |
    | Additional comments | Charlie was in a good mood           |
    | Call date & time    | 2017-08-24                           |
    | Phone number        | (310) 475-0859                       |
    | Direction           | Outgoing                             |
    | Duration            | 5:30                                 |

Scenario: Edit Call from entity view page
  Given I go to Customers/Contacts
  And click view Charlie in grid
  And I click "Update call log" on "Proposed Charlie to star in new film" in activity list
  And fill form with:
    | Owner               | Theresa                              |
    | Subject             | Offered Charlie a drink with me      |
    | Additional comments | Charlie was in a bad mood            |
    | Call date & time    | 2017-09-01                           |
    | Phone number        | (323) 879-6520                       |
    | Duration            | 00:03:25                             |
  When I press "Update call"
  Then I should see "Call saved" flash message
  And I collapse "Offered Charlie a drink with me" in activity list
  And I should see call with:
    | Subject             | Offered Charlie a drink with me      |
    | Additional comments | Charlie was in a bad mood            |
    | Call date & time    | 2017-09-01                           |
    | Phone number        | (323) 879-6520                       |
    | Direction           | Outgoing                             |
    | Duration            | 3:25

Scenario: Find and view call from call grid
  Given I go to Activities/Calls
  When I click view Offered Charlie a drink with me in grid
  Then the url should match "/call/view/\d+"

Scenario: Delete Call from entity view page
  Given I go to Customers/Contacts
  And click view Charlie in grid
  When I click "Delete Call log" on "Offered Charlie a drink with me" in activity list
  And confirm deletion
  Then I should see "Activity item deleted" flash message
  And there is no records in activity list
