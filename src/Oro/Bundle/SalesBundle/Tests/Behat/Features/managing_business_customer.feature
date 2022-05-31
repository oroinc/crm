@regression
@fixture-OroSalesBundle:business_customer_crud.yml
@fixture-OroLocaleBundle:ZuluLocalization.yml
@fixture-OroAddressBundle:CountryNameTranslation.yml
@fixture-OroContactBundle:LoadContactEntitiesFixture.yml

Feature: Managing business customer
  In order to check business customer crud
  As an Administrator
  I want to be able to business customer entity

  Scenario: Feature Background
    Given I login as administrator
    And I go to System / Configuration
    And I follow "System Configuration/General Setup/Localization" on configuration sidebar
    And I fill form with:
      | Enabled Localizations | [English, Zulu_Loc] |
      | Default Localization  | Zulu_Loc            |
    And I submit form

  Scenario: Update Sales Channel
    And I go to System/Channels
    And click edit Business Customers in grid
    When I save and close form
    Then I should see "Channel saved" flash message

  Scenario: Business customer create
    Given I login as administrator
    Then I go to Customers/ Business Customers
    And I click "Create Business Customer"
    Then Account is not required field
    And I fill "Business Customer Form" with:
      | Customer Name                | SimpsonCustomer        |
      | Phones                       | [11-11-11, 22-22-22]   |
      | Emails                       | [m1@ex.com]            |
      | Billing Address Country      | United StatesZulu      |
      | Billing Address State        | FloridaZulu            |
      | Billing Address Street       | Selma Ave              |
      | Billing Address City         | Los Angeles            |
      | Billing Address Postal Code  | 90028                  |
      | Shipping Address Country     | GermanyZulu            |
      | Shipping Address State       | BerlinZulu             |
      | Shipping Address Street      | test street            |
      | Shipping Address City        | test city              |
      | Shipping Address Postal Code | 123456                 |
    When I click on "Contact create new"
    And I click "maximize"
    And I fill "Create Contact Modal Form" with:
      | First name | Charlie             |
      | Last name  | Sheen               |
      | Emails     | [charlie@gmail.com] |
      | Phones     | [4157319375]        |
      | Primary    | true                |
      | Country    | GermanyZulu         |
      | State      | BerlinZulu          |
    And I click "Save" in modal window
    Then I should see "Saved successfully" flash message
    When I click on "Contact hamburger"
    Then should see following "Select Contact" grid:
      | First name   | Last name    | Email             | Phone      | Country           | State       | Zip/Postal Code |
      | Charlie      | Sheen        | charlie@gmail.com | 4157319375 | GermanyZulu       | BerlinZulu  |                 |
      | TestContact1 | TestContact1 | test1@test.com    | 5556668888 | GermanyZulu       | BerlinZulu  | 10001           |
      | TestContact2 | TestContact2 | test2@test.com    | 5556669999 | United StatesZulu | FloridaZulu | 10002           |
    When I click on Charlie in grid
    And I save and close form
    Then I should see Business Customer with:
      | Account          | SimpsonCustomer                          |
      | Customer Name    | SimpsonCustomer                          |
      | Channel          | Business Customers                       |
      | Phone            | [11-11-11, 22-22-22]                     |
      | Email            | [m1@ex.com]                              |
      | Contact          | Charlie Sheen                            |
      | Billing Address  | Selma Ave LOS ANGELES FL US 90028        |
      | Shipping Address | test street 123456 test city GermanyZulu |

  Scenario: Edit business customer
    Given I go to Customers/ Business Customers
    And I click Edit Bruce Customer in grid
    Then Account is a required field
    And I fill "Business Customer Form" with:
      | Account                      | Keanu Reeves           |
      | Customer Name                | Charlie Customer       |
      | Phones                       | [11-11-11, 22-22-22]   |
      | Emails                       | [edited@ex.com]        |
      | Billing Address Country      | GermanyZulu            |
      | Billing Address State        | BerlinZulu             |
      | Billing Address Street       | test street            |
      | Billing Address City         | test city              |
      | Billing Address Postal Code  | 123456                 |
      | Shipping Address Country     | United StatesZulu      |
      | Shipping Address State       | FloridaZulu            |
      | Shipping Address Street      | Selma Ave              |
      | Shipping Address City        | Los Angeles            |
      | Shipping Address Postal Code | 90028                  |
    When I save and close form
    Then I should see Business Customer with:
      | Account          | Keanu Reeves                             |
      | Customer Name    | Charlie Customer                         |
      | Channel          | Business Customers                       |
      | Email            | [edited@ex.com]                          |
      | Billing Address  | test street 123456 test city GermanyZulu |
      | Shipping Address | Selma Ave LOS ANGELES FL US 90028        |
    When I go to Customers/ Business Customers
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
    And I click "Delete Business Customer"
    And I confirm deletion
    Then there is no records in grid

  Scenario: Business customer create from lead
    Given I go to Sales/ Leads
    And I click "Create Lead"
    Then I add new Business Customer for Account field
    And I fill "SalesB2bCustomerForm" with:
      | Account         | Marge Simpson          |
      | Customer Name   | SimpsonCustomer        |
      | Phones          | [11-11-11]             |
      | Emails          | [m1@ex.com]            |
      | Country         | United StatesZulu      |
      | Street          | Selma Ave              |
      | City            | Los Angeles            |
      | Zip/Postal Code | 90028                  |
      | State           | California             |
    When I submit "Sales B2b Customer Form"
    Then Account field should has "Marge Simpson" value
    When I click "Cancel"
    And I go to Customers/ Business Customers
    Then I should see SimpsonCustomer in grid with following data:
      | Account         | Marge Simpson          |
      | Customer Name   | SimpsonCustomer        |
      | Email           | m1@ex.com              |
      | Channel         | Business Customers     |

  Scenario: Inline edit Business Customer
    Given I edit first record from grid:
      | Customer Name         | editedName             |
      | Email                 | m3@ex.com              |
      | Phone number          | 33-33-333              |
    Then I should see editedName in grid with following data:
      | Account               | Marge Simpson          |
      | Channel               | Business Customers     |
      | Email                 | m3@ex.com              |
      | Phone number          | 33-33-333              |

  Scenario: Import Business Customer
    Given I go to Customers/ Business Customers
    And there are one record in grid
    And I download "B2bCustomer" Data Template file
    And I fill template with data:
      | Channel Name       | Customer name | Lifetime sales value | Phones 1 Phone | Emails 1 Email    | Account Account name | Owner Username | Organization Name |
      | Business Customers | Jerry Coleman | 8508                 | 55-55-555      | imported@test.com | Jerry Coleman        | admin          | OroCRM            |
    When I import file
    And I reload the page
    Then there are 2 records in grid
