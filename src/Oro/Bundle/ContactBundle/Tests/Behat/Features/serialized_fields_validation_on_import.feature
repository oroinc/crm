@regression
@ticket-BAP-17916
Feature: Serialized fields validation on import
  In order to manage data of serialized fields
  As an Administrator
  I want to have validation messages when I import invalid values for serialized fields

  Scenario: Create serialized field with integer type
    Given I login as administrator
    And I go to System/ Entities/ Entity Management
    And filter Name as is equal to "Contact"
    And I click view Contact in grid
    And click "Create field"
    And I fill form with:
      | Field name   | field1           |
      | Storage type | Serialized field |
      | Type         | Integer          |
    And click "Continue"
    When I save and close form
    Then I should see "Field saved" flash message

  Scenario: Check import template message for integer field
    Given I go to Customers/ Contacts
    And there is no records in grid
    When I download "Contacts" Data Template file
    Then I see Id column
    And I see First name column
    And I see Last name column
    And I see Owner Username column
    And I see Emails 1 Email column
    And I see Phones 1 Phone column
    And I see Organization Name column
    And I see field1 column

  Scenario: Check validation message for integer type
    Given I fill template with data:
      | Id | First name | Last name | Owner Username | Emails 1 Email   | Phones 1 Phone | Organization Name | field1 |
      |    | Roy        | Greenwell | admin          | test@example.com | 765-538-2134   | ORO               | failed |
    When I import file
    Then Email should contains the following "Errors: 1 processed: 0, read: 1, added: 0, updated: 0, replaced: 0" text

  Scenario: Check that problem is only in integer value
    Given I fill template with data:
      | Id | First name | Last name | Owner Username | Emails 1 Email   | Phones 1 Phone | Organization Name | field1 |
      |    | Roy        | Greenwell | admin          | test@example.com | 765-538-2134   | ORO               | 1      |
    When I import file
    Then Email should contains the following "Errors: 0 processed: 1, read: 1, added: 1, updated: 0, replaced: 0" text
