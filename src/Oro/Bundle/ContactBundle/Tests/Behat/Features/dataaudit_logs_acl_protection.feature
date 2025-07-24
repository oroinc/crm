@regression
@ticket-BAP-9497
@ticket-BAP-15092

Feature: DataAudit logs acl protection

  Scenario: Create different window session
    Given sessions active:
    | Admin |first_session |
    | User  |second_session|

  Scenario: DataAudit logs acl protection
    Given I proceed as the Admin
    And I login as administrator
    And go to System/ User Management/ Roles
    And click "Create Role"
    And fill form with:
    |Role|RoleForTest|
    And save and close form
    And go to System/ User Management/ Users
    And click "Create User"
    And fill "Create User Form" with:
    |Enabled            |Enabled           |
    |Username           |testUser1@test.com|
    |Password           |testUser1@test.com|
    |Re-Enter Password  |testUser1@test.com|
    |First Name         |FName             |
    |Last Name          |LName             |
    |Primary Email      |testUser1@test.com|
    |OroCRM Organization|true              |
    |RoleForTest Role   |true              |
    And save and close form
    And go to System/ User Management/ Roles
    When click edit "RoleForTest" in grid
    And select following permissions:
      | Audit   | View:Global |
    And select following permissions:
      | Contact | View:Global | Create:Global | Edit:Global | Delete:Global | Assign:Global |
    And save and close form
    And I proceed as the User
    And login to dashboard as "testUser1@test.com" user
    And go to Customers/ Contacts
    And click "Create Contact"
    And fill form with:
    |First name| testFname|
    |Last name | testLname|
    And save and close form
    And go to System/ Data Audit
    Then I should see following grid:
    |Create|1|Contact|testFname testLname|FName LName - admin@example.com|OroCRM|

  Scenario: Ensure that Change History popup works
    Given I proceed as the User
    Then I go to Customers/ Contacts
    And I click View testFname in grid
    And I click "Change History"
    Then number of records in "Audit History Grid" should be 1
    And I close ui dialog
