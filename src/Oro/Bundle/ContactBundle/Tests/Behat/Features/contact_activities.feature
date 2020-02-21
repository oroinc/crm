@fixture-OroUserBundle:user.yml
@ticket-CRM-8708

Feature: Contact activities
  In order to have ability work with contacts
  As administrator
  I need to have ability to create, view, update contact activities

  Scenario: Create different window session
    Given sessions active:
      | Admin | first_session  |
      | User  | second_session |

  Scenario: Create contact
    When I proceed as the Admin
    And I login as administrator
    And I go to Customers/Contacts
    And click "Create Contact"
    And I fill form with:
      | First name | Charlie |
      | Last name  | Sheen   |
    When save and close form
    Then I should see "Contact saved" flash message

  Scenario: Add note to contact entity
    And follow "More actions"
    And click "Add note"
    And fill "Note Form" with:
      | Message    | <strong>Charlie works hard</strong> |
    And I scroll to "Add Note Button"
    And I click "Add Note Button"
    Then I should see "Note saved" flash message
    And I should see "Charlie works hard" note in activity list
    And I should not see "<strong>Charlie works hard</strong>"
    When I click on "First Activity Item"
    Then I should not see "<strong>Charlie works hard</strong>"

  Scenario: Create "Calendar Event" activity as "Admin" and assert createdBy and updatedBy values
    And I follow "More actions"
    And I follow "Add Event"
    And fill form with:
      | Title | Calendar event with Charlie |
    When click "Save"
    Then I should see "Calendar event saved" flash message
    And I should see "Calendar event with Charlie" event in activity list
    And I should see "Calendar event added by John Doe" event in activity list

  Scenario: Create "Task" activity as "Admin" and assert createdBy and updatedBy values
    And I go to Activities/Tasks
    And I click "Create Task"
    And fill "Task Form" with:
      | Subject     | Contact with Charlie |
      | Description | Offer him a role     |
      | Due date    | <DateTime:+ 1 day>   |
      | Status      | Open                 |
      | Priority    | Normal               |
    And set Reminders with:
      | Method        | Interval unit | Interval number |
      | Flash message | minutes       | 30              |
    When save and close form
    Then I should see "Task saved" flash message
    When I click "Add Context"
    And I select "Contact" context
    And I click on Charlie in grid
    When I go to Customers/Contacts
    And click view Charlie in grid
    Then I should see "Contact with Charlie" task in activity list
    And I should see "Task assigned by John Doe, updated by John Doe" task in activity list

  Scenario: Update "Task" activity as "User" and assert createdBy and updatedBy values
    Given I proceed as the User
    And I login as "charlie" user
    And I go to Activities/Tasks
    And click edit Contact with Charlie in grid
    And fill form with:
      | Description | Offer him a new role |
      | Priority    | high                 |
    When save and close form
    Then I should see "Task saved" flash message
    When I go to Customers/Contacts
    And click view Charlie in grid
    Then I should see "Contact with Charlie" task in activity list
    And I should see "Task assigned by John Doe, updated by Charlie Sheen" task in activity list

  Scenario: Update "Calendar Event" activity as "User" and assert createdBy and updatedBy values
    And I click "Update Calendar event" on "Calendar event with Charlie" in activity list
    And fill form with:
      | Description | Update event description |
    When click "Save"
    Then I should see "Calendar event saved" flash message
    And I should see "Calendar event with Charlie" event in activity list
    And I should see "Calendar event added by John Doe, updated by Charlie Sheen" event in activity list
