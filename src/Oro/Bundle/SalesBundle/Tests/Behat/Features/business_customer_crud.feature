@fixture-business_customer_crud.yml
Feature: Managing business customer
  In order to check business customer crud
  As an Administrator
  I want to be able to business customer entity

  Scenario: Business customer create
    Given I login as administrator
    Then I go to Customers/ Business Customers
    And I press "Create Business Customer"
    And I fill form with:
      | Account         | Marge Simpson          |
      | Customer Name   | SimpsonCustomer        |
      | Phones          | [11-11-11, 22-22-22]   |
      | Emails          | [m1@ex.com]            |
      | Country         | United States          |
      | Street          | Selma Ave              |
      | City            | Los Angeles            |
      | Zip/Postal Code | 90028                  |
      | State           | California             |
    When I save and close form
    Then I should see Business Customer in grid with:
      | Account         | Marge Simpson                |
      | Customer Name   | SimpsonCustomer              |
      | Channel         | Business Customers           |
      | Phone           | [11-11-11, 22-22-22]   |
      | Email           | [m1@ex.com]            |

  Scenario: Edit business customer
    Given I go to Customers/ Business Customers
    And I click Edit Bruce Customer in grid
    And I fill form with:
      | Account         | Keanu Reeves           |
      | Customer Name   | Charlie Customer       |
      | Phones          | [11-11-11, 22-22-22]   |
      | Emails          | [edited@ex.com]        |
      | Country         | United States          |
      | Street          | Selma Ave              |
      | City            | Los Angeles            |
      | Zip/Postal Code | 90028                  |
      | State           | California             |
    When I save and close form
    And I go to Customers/ Business Customers
    Then I should see Charlie Customer in grid with following data:
      | Account         | Keanu Reeves         |
      | Customer Name   | Charlie Customer     |
      | Channel         | Business Customers   |
      | Email           | edited@ex.com        |

  Scenario: Deleting business customer
    Given I click Delete SimpsonCustomer in grid
    And I confirm deletion
    Then I should see "Item deleted" flash message
    And there is one record in grid
    When I click view Charlie Customer in grid
    And I press "Delete Business Customer"
    And I confirm deletion
    Then there is no records in grid

  Scenario: Business customer create from lead
    Given I go to Sales/ Leads
    And I press "Create Lead"
    Then I add new Business Customer for Account field
    And I fill "SalesB2bCustomerForm" with:
      | Account         | Marge Simpson          |
      | Customer Name   | SimpsonCustomer        |
      | Phones          | [11-11-11]             |
      | Emails          | [m1@ex.com]            |
      | Country         | United States          |
      | Street          | Selma Ave              |
      | City            | Los Angeles            |
      | Zip/Postal Code | 90028                  |
      | State           | California             |
    When I submit "Sales B2b Customer Form"
    Then Account field should has "Marge Simpson" value
    When I press "Cancel"
    And I go to Customers/ Business Customers
    Then I should see SimpsonCustomer in grid with following data:
      | Account         | Marge Simpson          |
      | Customer Name   | SimpsonCustomer        |
      | Email           | m1@ex.com              |
      | Channel         | Business Customers     |

  Scenario: Inline edit Business Customer
    Given I edit first record from grid:
      | Customer Name         | editedName             |
      | Lifetime sales value  | 22                     |
      | Email                 | m3@ex.com              |
      | Phone number          | 33-33-333              |
    Then I should see editedName in grid with following data:
      | Account               | Marge Simpson          |
      | Channel               | Business Customers     |
      | Lifetime sales value  | $22.00                 |
      | Email                 | m3@ex.com              |
      | Phone number          | 33-33-333              |
