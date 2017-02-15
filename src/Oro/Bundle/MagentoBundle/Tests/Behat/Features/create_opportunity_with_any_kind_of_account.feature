Feature: Create opportunity with different accounts type
  In order to create opportunity
  As sales rep
  I need ability to choose any kind of account

  Scenario: Required fields
    Given I login as administrator
    And "First Sales Channel" is a channel with enabled Business Customer, Magento Customer entities
    And two users charlie and samantha exists in the system
    And user have "User" permissions for "View" "Magento Customer, Account, Business Customer" entities
    And they has their own Accounts and Customers
    And I open Opportunity Create page
    Then Opportunity Name is a required field
    Then Account is a required field
    And press "Cancel"

  Scenario Outline: Choose Account and Business Customer
    Given I login as "<user>" user
    And I open Opportunity Create page
    And Accounts in the control are filtered according to <user> ACL permissions
    And press "Cancel"
    Examples:
      | user     |
      | charlie  |
      | samantha |

  Scenario Outline: Choose Magento Customer
    Given I login as "<user>" user
    And I open Opportunity Create page
    Then Magento Customers in the control are filtered according to <user> ACL permissions
    And press "Cancel"
    Examples:
      | user     |
      | charlie  |
      | samantha |
