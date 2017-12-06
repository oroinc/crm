@regression
@ticket-BAP-15838
Feature: Email notification to any email field
  As administrator
  When I configure email notifications
  I want to be able to use any field marked as email in entity config

  Scenario: Administrator checks out-of-box "Contact information" value of "Contact request" email address
    Given I login as administrator
    And I go to System/ Entities/ Entity Management
    And I filter Name as contains "ContactRequest"
    And click view "Contact Request" in grid
    When click edit "emailAddress" in grid
    Then I should see "Contact Information Email"

Scenario: Administrator marks as "Email" organization name field
    Given I click "Cancel"
    And I click edit "organizationName" in grid
    And I fill form with:
      | Contact Information | Email |
    When I save and close form
	Then I should see "Field saved" flash message

Scenario: Administrator creates string field with "Database column" storage type and marks it as "Email"
    Given I click "Create Field"
    And I fill form with:
      | Field name   | TableColumnStringField |
      | Storage type | Table column           |
      | Type         | String                 |
    And I click "Continue"
    And I fill form with:
      | Contact Information | Email |
    When I save and close form
	Then I should see "Field saved" flash message

Scenario: Administrator creates text field with "Serialized field" storage type and marks it as "Email"
    Given I click "Create Field"
    And I fill form with:
      | Field name   | SerializedTextField |
      | Storage type | Serialized field    |
      | Type         | Text                |
    And I click "Continue"
    And I fill form with:
      | Contact Information | Email |
    When I save and close form
	Then I should see "Field saved" flash message

Scenario: Schema updating
    When I click update schema
    Then I should see Schema updated flash message

Scenario: Administrator creates notification rule for "Contact request" and picks "Email" fields as recipients
    Given I go to System/ Emails/ Notification Rules
    And I click "Create Notification Rule"
    When I fill form with:
      | Entity name | Contact Request                     |
      | Event name  | Entity create                       |
      | Template    | contact_request_create_notification |
    And check "Opportunity"
    And check "Contact Email" element
    And check "Organization name"
    And check "TableColumnStringField"
    And check "SerializedTextField"
    Then I save and close form

Scenario: Administrator creates contact request and fills "Email" fields
    Given I go to Activities/ Contact Requests
    And I click "Create Contact Request"
    And I fill form with:
      | First Name               | Albert         |
      | Last Name                | Bobel          |
      | Organization Name        | test2@test.com |
      | Preferred Contact Method | Email          |
      | Email                    | test1@test.com |
      | Comment                  | test3@test.com |
      | TableColumnStringField   | test4@test.com |
      | SerializedTextField      | test5@test.com |
    When I save and close form
    Then I should see "Contact request has been saved successfully" flash message

@skip
Scenario: Administrator checks incoming emails
  Given I go to mailbox
  Then I should see incoming email with some message about Contact Request creation
  And I repeat for each email address

Scenario: Administrator removes "Email" item from used fields
    Given I go to System/ Entities/ Entity Management
    And I filter Name as contains "ContactRequest"
    And click view "Contact Request" in grid
    And click edit "emailAddress" in grid
    And I fill form with:
      | Contact Information ||
    And I save and close form
    And click edit "organizationName" in grid
    And I fill form with:
      | Contact Information ||
    And I save and close form
    And click edit "TableColumnStringField" in grid
    And I fill form with:
      | Contact Information ||
    And I save and close form
    And click edit "SerializedTextField" in grid
    And I fill form with:
      | Contact Information ||
    And I save and close form

Scenario: Administrator checks state of previously created notification rule
    Given I go to System/ Emails/ Notification Rules
    When I click edit "Contact Request" in grid
    Then I should not see an "Contact Email" element
    And I should not see "Organization name"
    And I should not see "TableColumnStringField"
    And I should not see "SerializedTextField"
