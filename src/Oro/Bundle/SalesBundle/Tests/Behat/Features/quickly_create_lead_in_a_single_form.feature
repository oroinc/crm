Feature: Quickly create Lead in a single form
  In order to decrease time for Lead creation
  As a Sales rep
  I want to create Lead in a single form with minimum requirements

  Scenario: Background
    Given I login as administrator
    And "Sales Channel" is a channel with enabled Business Customer entity
    And I open Lead Create page
    Then Lead Name is a required field

  Scenario: Create new Lead
    Given I fill form with:
      | Lead Name  | Acme company                 |
      | First Name | Charlie                      |
      | Last Name  | Sheen                        |
      | Phones     | [11-11-11, 22-22-22]         |
      | Emails     | [lead1@ex.com, lead2@ex.com] |
    And I fill in address:
      | Primary         | check         |
      | Country         | United States |
      | Street          | Selma Ave     |
      | City            | Los Angeles   |
      | Zip/Postal Code | 90028         |
      | State           | California    |
    And add new address with:
      | Primary         | check               |
      | Country         | Ukraine             |
      | Street          | Myronosytska 57     |
      | City            | Kharkiv             |
      | Zip/Postal Code | 61000               |
      | State           | Kharkivs'ka Oblast' |
    When I save and close form
    Then I should be on "Acme company" Lead view page
    And should see Lead in grid with:
      | First Name | Charlie                      |
      | Last Name  | Sheen                        |
      | Phones     | [11-11-11, 22-22-22]         |
      | Emails     | [lead1@ex.com, lead2@ex.com] |
    And two addresses should be in page
    And Ukraine address must be primary

  Scenario: Edit Lead
    Given I edit entity
    When I fill form with:
      | First Name | John                         |
      | Last Name  | Doe                          |
      | Status     | Qualified                    |
      | Phones     | [33-33-33, 44-44-44]         |
      | Emails     | [lead3@ex.com, lead4@ex.com] |
    And save and close form
    Then I should be on Lead View page
    And should see Lead in grid with:
      | First Name | John                         |
      | Last Name  | Doe                          |
      | Phones     | [33-33-33, 44-44-44]         |
      | Emails     | [lead3@ex.com, lead4@ex.com] |

  Scenario: Inline edit Lead

  Scenario: Import Lead
