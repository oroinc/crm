Feature: Import opportunity feature
  In order to simplify work with opportunities
  As crm user
  I want to import/export opportunities data

  Scenario: Data Template for Opportunity
    Given I login as administrator
    And "First Sales Channel" is a channel with enabled Opportunity, Lead, Business Customer entities
    And I go to Opportunity Index page
    When I download Data Template file
    Then I don't see Business Customer Name column
    And I see Account Customer name column

  Scenario: Import Opportunity with Account and Customer
    Given crm has Acme Account with Charlie and Samantha customers
    And I fill template with data:
      | Account Customer name | Channel Name        | Opportunity name | Status Id   |
      | Charlie               | First Sales Channel | Opportunity one  | in_progress |
      | Samantha              | First Sales Channel | Opportunity two  | in_progress |
#    And Account is specified in  the import file
#    And CRM has Account with that name
#    And Account has relation to Customer with that name
    When I import file
    Then new Opportunity is created with relation to specified Account

#  Scenario: Import Opportunity with new Account
#    Given CRM has 'sales channels'
#    And Account has Business Customers
#    And Account specified in the import file
#    And CRM has no Account with that name
#    When I import file
#    Then new Account and Customer are created
#    And Customer name is equal to Account name
#    And new Opportunity is created with relation to Account
#
#  Scenario: Import Opportunity with new Customer
#    Given CRM has 'sales channels'
#    And Account has Business Customers
#    And Account specified in the import file
#    And CRM has an Account with that name
#    And Account has no Customers
#    When I import file
#    Then new Customer is created
#    And Customer name is equal to Account name
#    And new Opportunity is created with relation to Account
#
#  Scenario: Import Opportunity with no Account
#    Given CRM has 'sales channels'
#    And Account has Business Customers
#    And Account not specified in the import file
#    And CRM has no Account with that name
#    When I import file
#    Then I see validation message 'Error in row #1. name: This value should not be blank'
#
#  Scenario: Import Opportunity if Account and Customer have different names
#    Given CRM has 'sales channels'
#    And Account has Business Customers
#    And Account specified in the import file
#    And CRM has no Account with that name
#    And Account has relation to Customers with different name
#    When I import file
#    Then I see validation message 'Error in row #1. name: Customer name is needed'
