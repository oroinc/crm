@ticket-BAP-17916
@ticket-BAP-18778
@fixture-OroContactBundle:LoadContactEntitiesFixture.yml
@fixture-OroAccountBundle:LoadAccountEntitiesFixture.yml
Feature: Extended table fields validation on import
  In order to manage data of serialized fields
  As an Administrator
  I want to have validation messages when I import invalid values for table column fields

  Scenario: Create serialized field with integer type
    Given I login as administrator
    And I go to System/ Entities/ Entity Management
    And filter Name as is equal to "Contact"
    And I click view Contact in grid
    And click "Create field"
    And I fill form with:
      | Field name   | field1       |
      | Storage type | Table column |
      | Type         | Integer      |
    And click "Continue"
    When I save and close form
    Then I should see "Field saved" flash message

  Scenario: Create ManyToOne relation
    When click "Create field"
    And I fill form with:
      | Field name   | ext_account  |
      | Storage Type | Table column |
      | Type         | Many to one  |
    And I click "Continue"
    And I fill form with:
      | Target Entity | Account      |
      | Target Field  | Account name |
      | Show On Form  | Yes          |
    And I save and close form
    Then I should see "Field saved" flash message

  Scenario: Update schema
    When I click update schema
    Then I should see Schema updated flash message

  Scenario: Check import template message for integer field
    Given I go to Customers/ Contacts
    When I download "Contacts" Data Template file
    Then I see Id column
    And I see First name column
    And I see Last name column
    And I see Owner Username column
    And I see Emails 1 Email column
    And I see Phones 1 Phone column
    And I see Organization Name column
    And I see field1 column
    And I see ext_account Account name column

  Scenario: Check validation message for integer type
    Given I fill template with data:
      | Id | First name | Last name | Owner Username | Emails 1 Email   | Phones 1 Phone | Organization Name | field1 | ext_account Account name |
      |    | Roy        | Greenwell | admin          | test@example.com | 765-538-2134   | ORO               | failed | account_1                |
    When I import file
    Then Email should contains the following "Errors: 1 processed: 0, read: 1, added: 0, updated: 0, replaced: 0" text

  Scenario: Check that problem is only in integer value
    Given I fill template with data:
      | Id | First name | Last name | Owner Username | Emails 1 Email   | Phones 1 Phone | Organization Name | field1 | ext_account Account name |
      |    | Roy        | Greenwell | admin          | test@example.com | 765-538-2134   | ORO               | 1      | account_1                |
    When I import file
    Then Email should contains the following "Errors: 0 processed: 1, read: 1, added: 1, updated: 0, replaced: 0" text

  Scenario: Check validation doesn't block valid entities import
    Given I go to Customers/ Contacts
    And I download "Contacts" Data Template file
    And I fill template with data:
      | Id | First name   | Last name    | Owner Username | Emails 1 Email    | Phones 1 Phone | Organization Name | field1 | ext_account Account name | Accounts Default Contact 1 Account name | Accounts 1 Account name |
      |    | Contact1     | LN           | admin          | test1@example.com | 123-538-2134   | ORO               | fail   | account_1                | account_1                               | account_1               |
      |    | Contact2     | LN           | admin          | test2@example.com | 456-538-2134   | ORO               | 1      | account_1                | account_1                               | account_1               |
      |    | TestContact1 | TestContact1 | admin          | test3@example.com | 456-538-2135   | ORO               | fail   | account_1                | account_1                               | account_1               |
      |    | TestContact2 | TestContact2 | admin          | test@example.com  | 765-538-2134   | ORO               | 2      | account_1                | account_1                               | account_1               |
    When I import file
    Then Email should contains the following "Errors: 2 processed: 2, read: 4, added: 1, updated: 0, replaced: 1" text
