@regression
@smoke
@fixture-OroCRMBundle:activities-smoke-e2e.yml
# @TODO split this feature and move to the regular test suite, or create small features in addition there
Feature: Activities
  In order to check Activity entity on admin panel
  As a Admin
  I want to start end to end test

  Scenario: Create a Task record in the Tasks grid
    Given login as administrator
    When go to Activities/ Tasks
    And click "Create Task"
    And fill "Task Form" with:
      | Subject     | Test1                   |
      | Description | test description        |
      | Due date    | <DateTime:+1 day 12:00> |
      | Status      | Open                    |
      | Priority    | Normal                  |
    And set Reminders with:
      | Method        | Interval unit | Interval number |
      | Flash message | minutes       | 30              |
    And save and close form
    Then should see "Task saved" flash message
    And go to Activities/ Tasks
    And I should see following grid:
      | Subject | Due date     | Status | Priority | Assigned To |
      | Test1   | +1 day 12:00 | Open   | Normal   | John Doe    |
    When click My Tasks in user menu
    Then I should see following grid:
      | Subject | Due date     | Status | Priority |
      | Test1   | +1 day 12:00 | Open   | Normal   |

  Scenario: Add a Task for Another Record
    Given go to Sales/ Leads
    And click "Create Lead"
    And fill form with:
      | Lead name | OroInc |
    And save and close form
    When click "More actions"
    And click "Add task"
    And fill "Task Form" with:
      | Subject     | Sprint Demo             |
      | Description | test description        |
      | Due date    | <DateTime:+1 day 12:00> |
      | Status      | Open                    |
      | Priority    | Normal                  |
    And set Reminders with:
      | Method        | Interval unit | Interval number |
      | Flash message | hours         | 30              |
    And click "Create Task"
    Then should see "Task created successfully" flash message
    And should see "Sprint Demo - test description"
    And should see "Task assigned by John Doe"
    And go to Activities/ Tasks
    And click view "Sprint Demo" in grid
    And should see "Context OroInc"

  Scenario: Assign a Task to a User
    Given go to System/ User Management/ Users
    And click view "Charlie1@example.com" in grid
    When click "More actions"
    And click "Assign task"
    And fill "Task Form" with:
      | Subject     | SalesRep Task           |
      | Description | test description        |
      | Due date    | <DateTime:+1 day 12:00> |
      | Status      | Open                    |
      | Priority    | Normal                  |
    And set Reminders with:
      | Method | Interval unit | Interval number |
      | Email  | hours         | 30              |
    And click "Create Task"
    Then should see "Task created successfully" flash message
    And I should see following grid:
      | Subject       | Due date     | Status | Priority |
      | SalesRep Task | +1 day 12:00 | Open   | Normal   |
    And go to Activities/ Tasks
    And click view "SalesRep Task" in grid
    And should see "Assigned To: Charlie Sheen (Main)"

  Scenario:View and Manage Tasks on the view page of a record
    Given go to Sales/ Leads
    And click "Create Lead"
    And fill form with:
      | Lead name | SomeCompany |
    And save and close form
    And go to Sales/ Leads
    And click view "OroInc" in grid
    When I hover on "Activity Dropdown Menu"
    And click "Update Task"
    And fill form with:
      | Subject | Sprint Nemo |
    And click "Update Task"
    Then should see "Task created successfully" flash message
    And should see "Sprint Nemo - test description"
    When I hover on "Activity Dropdown Menu"
    And click "Add Context"
    And click "Context Entity Dropdown"
    And click on "Lead"
    And click on SomeCompany in grid
    And go to Activities/ Tasks
    And click view "Sprint Nemo" in grid
    Then should see "Context OroInc SomeCompany"
    When click "Delete"
    And click "Yes, Delete"
    Then I should see "Task deleted" flash message
    And go to Sales/ Leads
    And click view "OroInc" in grid
    And should not see "Sprint Nemo - test description"

  Scenario:Create a call record in the Calls grid
    Given go to Activities/ Calls
    And click "Log call"
    And fill "Log Call Form" with:
      | Subject             | Call to Someone                           |
      | Additional comments | Offered $40 discount on her next purchase |
      | Call date & time    | <DateTime:2016-10-31 08:00:00>            |
      | Phone number        | 0501468825                                |
      | Direction           | Outgoing                                  |
      | Duration            | 60s                                       |
    And save and close form
    Then should see "Call saved" flash message
    And go to Activities/ Calls
    And I should see following grid:
      | Subject         | Phone number | Call date & time      | Contexts |
      | Call to Someone | 0501468825   | Oct 31, 2016, 8:00 AM | John Doe |

  Scenario:Add a Task for Another Record
    Given go to Sales/ Leads
    And click view "OroInc" in grid
    When click "More actions"
    And click "Log call"
    And fill "Log Call Form" with:
      | Subject             | Call for lead                             |
      | Additional comments | Offered $50 discount on her next purchase |
      | Call date & time    | <DateTime:2016-11-21 08:00:00>            |
      | Phone number        | 0503505566                                |
      | Direction           | Incoming                                  |
      | Duration            | 30s                                       |
    And click "Log call"
    Then should see "Call saved" flash message
    And should see "Call for lead - Offered $50 discount on her next purchase"
    And should see "Call logged by John Doe"
    And go to Activities/ Calls
    And click view "Call for lead" in grid
    And should see "Context OroInc"

  Scenario:View and Manage Calls on the view page of a record
    Given go to Sales/ Leads
    And click view "OroInc" in grid
    When I hover on "Activity Dropdown Menu"
    And click "Update Call log"
    And I fill form with:
      | Phone number | 0503504444 |
    And click "Update call"
    Then should see "Call saved" flash message
    And click on "Call log accordion"
    And should see "Phone Number 0503504444"
    And should not see "Phone Number 0503505566"
    And go to Activities/ Calls
    And I should see following grid:
      | Subject         | Phone number | Call date & time      | Contexts        |
      | Call for lead   | 0503504444   | Nov 21, 2016, 8:00 AM | OroInc John Doe |
      | Call to Someone | 0501468825   | Oct 31, 2016, 8:00 AM | John Doe        |

  Scenario:View and Manage Calls on the view page of a record
    Given go to Activities/ Cases
    And click "Create Case"
    And I fill form with:
      | Subject     | Case subject                  |
      | Description | Case for behat testing        |
      | Resolution  | Create through form and check |
      | Source      | Web                           |
      | Status      | Open                          |
      | Priority    | High                          |
    And save and close form
    And should see "Case saved" flash message
    And click "Add Comment"
    And type "some comment" in "Message"
    And click "Save"
    And should see "Comment saved" flash message
    And click "Edit Comment"
    And type "new data" in "Message"
    And click "Save"
    And should see "Comment saved" flash message
    And should see "new data"
    And go to Activities/ Cases
    And click edit "Case subject" in grid
    When I fill form with:
      | Owner | Charlie Sheen |
    And save and close form
    And should see "Case saved" flash message
    And go to System/ User Management/ Users
    And click view "Charlie Sheen" in grid
    Then I should see following "User Cases Grid" grid:
      | Case ID | Subject      | Status | Priority |
      | 1       | Case subject | Open   | High     |

  Scenario: Create a calendar event in the Calendar Events grid
    Given go to Activities/ Calendar Events
    And click "Create Calendar event"
    When I fill "Event Form" with:
      | Title         | All day no repeat Event     |
      | Start         | <DateTime:+2 days 12:00 AM> |
      | End           | <DateTime:+4 days 12:00 AM> |
      | All-Day Event | true                        |
      | Repeat        | false                       |
      | Description   | testfull desc               |
      | Guests        | John Doe                    |
      | Color         | Cornflower Blue             |
    And set Reminders with:
      | Method        | Interval unit | Interval number |
      | Email         | days          | 1               |
      | Flash message | minutes       | 30              |
    And I save and close form
    And click "Notify"
    Then should see "Calendar event saved" flash message
    When click "Add Context"
    And click "Context Entity Dropdown"
    And click on "Lead"
    And click on SomeCompany in grid
    Then I should see "The context has been added" flash message
    And should see "Context SomeCompany"
    And go to Activities/ Calendar Events
    And should see following grid:
      | Title                   | Calendar | Start            | End              | Recurrent | Recurrence |
      | All day no repeat Event | John Doe | +2 days 12:00 AM | +4 days 11:59 PM | No        |            |

  Scenario: Add an event for Another Record
    Given go to Sales/ Leads
    And click view "OroInc" in grid
    When click "More actions"
    And click "Add Event"
    And I fill "Event Form" with:
      | Title         | New event                   |
      | Start         | <DateTime:+2 days 12:00 AM> |
      | End           | <DateTime:+4 days 12:00 AM> |
      | All-Day Event | true                        |
      | Repeat        | false                       |
      | Description   | testfull desc               |
      | Guests        | John Doe                    |
      | Color         | Cornflower Blue             |
    And set Reminders with:
      | Method        | Interval unit | Interval number |
      | Email         | days          | 1               |
      | Flash message | hours         | 1               |
    And click "Save"
    Then should see "Calendar event saved" flash message
    And should see "Calendar event added by John Doe"
    And should see "New event - testfull desc"
    And go to Activities/ Calendar Events
    And click view "New event" in grid
    And should see "Context OroInc"

  Scenario: Create an event from My Calendar
    Given click My Calendar in user menu
    And click on "Empty slot"
    When I fill "Event Form" with:
      | Title         | Stand-Up            |
      | Start         | <DateTime:+2 hours> |
      | All-Day Event | false               |
      | Repeat        | false               |
      | Description   | testfull desc       |
      | Guests        | Charlie Sheen       |
      | Color         | Cornflower Blue     |
    And set Reminders with:
      | Method        | Interval unit | Interval number |
      | Email         | hours         | 1               |
      | Flash message | minutes       | 5               |
    And click "Save"
    And click "Notify"
    Then I should see "Stand-Up" in calendar with:
      | Description   | testfull desc                        |
      | Guests        | Charlie Sheen (Charlie1@example.com) |
      | All-day event | No                                   |

  Scenario: Manage Calendar Events
    Given go to Activities/ Calendar Events
    And click edit "Stand-Up" in grid
    When I fill "Event Form" with:
      | All-Day Event  | true     |
      | Repeat         | true     |
      | Guests         | John Doe |
      | EndsRecurrence | After:1  |
    And save and close form
    And click "Notify"
    Then should see Event with:
      | Title         | Stand-Up                                  |
      | Description   | testfull desc                             |
      | All-Day Event | Yes                                       |
      | Guests        | John Doe - Organizer                      |
      | Recurrence    | Daily every 1 day, end after 1 occurrence |
    And go to Activities/ Calendar Events
    And I sort grid by "Title"
    And should see following grid:
      | Title                   | Calendar | Recurrent | Recurrence                                |
      | All day no repeat Event | John Doe | No        |                                           |
      | New event               | John Doe | No        |                                           |
      | Stand-Up                | John Doe | Yes       | Daily every 1 day, end after 1 occurrence |
