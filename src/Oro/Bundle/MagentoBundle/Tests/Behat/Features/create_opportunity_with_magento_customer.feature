Feature: Create opportunity with magento customer

  Scenario: Required fields
    Given I login as administrator
    And "First Sales Channel" is a channel with enabled Business Customer, Magento Customer entities
    And two users charlie and samantha exists in the system
    And user have "User" permissions for "View" "Magento Customer" entity
    And they has their own Accounts and Customers
    And I open Opportunity Create page
    Then Opportunity Name is a required field
    And press "Cancel"

  @skip
  Scenario Outline: Create Opportunity with 1 'sales channel'
    Given I login as "<user>" user
    And I open Opportunity Create page
    And Accounts and Customers in the control are filtered according to <user> ACL permissions
    And press "Cancel"
    Examples:
      | user     |
      | charlie  |
      | samantha |

  Scenario: One more sales channel
    Given CRM has second sales channel with Accounts and Magento Customers

  Scenario Outline: Create Opportunity with more than 1 'sales channel'
    Given I login as "<user>" user
    And I open Opportunity Create page
    Then Accounts and Customers in the control are filtered by selected sales channel and <user> ACL permissions
    And press "Cancel"

  Examples:
    | user     |
    | charlie  |
    | samantha |
