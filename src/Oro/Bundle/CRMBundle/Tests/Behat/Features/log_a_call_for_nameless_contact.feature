@ticket-CRM-9070
@fixture-OroCRMBundle:nameless_contact.yml

Feature: Log a call for nameless contact
  In order to have ability work with contacts
  As administrator
  I need to have ability log a call for nameless contact

  Scenario: Log a call for nameless contact
    Given I login as administrator
    And I go to Customers/Contacts
    And I click "View" on first row in grid
    And follow "More actions"
    And follow "Log call"
    And fill "Log Call Form" with:
      | Subject             | Very important call           |
      | Additional comments | Propose something interesting |
      | Call date & time    | <DateTime:2017-08-24 11:00>   |
      | Duration            | 00:05:30                      |
    When I click "Log call"
    Then I should see "Call saved" flash message
    And should see "Very important call" call in activity list

  Scenario: View Call in activity list
    Given I go to Activities/ Calls
    And I click "View" on first row in grid
    Then I should see call with:
      | Subject             | Very important call           |
      | Additional comments | Propose something interesting |
      | Call date & time    | Aug 24, 2017                  |
      | Call date & time    | 11:00 AM                      |
      | Phone number        | (310) 475-0859                |
      | Direction           | Outgoing                      |
      | Duration            | 5:30                          |
