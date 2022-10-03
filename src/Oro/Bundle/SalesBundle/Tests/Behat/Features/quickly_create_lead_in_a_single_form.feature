@ticket-CRM-5308
@ticket-BAP-21510
@automatically-ticket-tagged
@fixture-OroSalesBundle:lead.yml
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
    And add new address with:
      | Primary         | false               |
      | Country         | United States       |
      | Street          | 45600 Marion Drive  |
      | City            | Winter Haven        |
      | Zip/Postal Code | 33830               |
      | State           | Florida             |
    When I save and close form
    Then I should be on "Acme company" Lead view page
    And should see Lead in grid with:
      | First Name | Charlie                      |
      | Last Name  | Sheen                        |
      | Phones     | [11-11-11, 22-22-22]         |
      | Emails     | [lead1@ex.com, lead2@ex.com] |
    And 3 addresses should be in page
    And Ukraine address must be primary

  Scenario: Delete address from lead
    When I delete 45600 Marion Drive address
    And click "Yes, Delete"
    Then two addresses should be in page
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

  Scenario Outline: Validation website data
    When I go to Sales/ Leads
    And I should see "Bruce"
    And I click "Edit" on row "Willis" in grid
    And I fill form with:
      | Website | <Website> |
    Then I should see "This value is not a valid URL. Allowed URL protocols are: http, https."
    And I click "Cancel"

    Examples:
      | Website                            |
      | sample-string                      |
      | unsupported-protocol://sample-site |
      | javascript:alert(1)                |
      | jAvAsCrIpt:alert(1)                |

  Scenario: Inline edit Lead
    Given I go to Sales/ Leads
    When I edit first record from grid:
      | Lead Name    | editedName          |
      | Status       | Qualified           |
      | First Name   | editedFirstName     |
      | Last Name    | editedLastName      |
      | Email        | edit@example.com    |
      | Phone Number | +111111111111       |
      | Owner        | Marge Marge Simpson |
    Then I should see editedName in grid with following data:
      | First Name   | editedFirstName    |
      | Last Name    | editedLastName     |
      | Phone Number | +111111111111      |
      | Email        | edit@example.com   |
      | Owner        | Marge Marge Simpson|
      | Status       | Qualified          |

  Scenario: Delete lead
    Given I click on editedName in grid
    And I should be on "editedName" Lead View page
    When I click "Delete Lead"
    And confirm deletion
    Then I should see "Lead deleted" flash message
    And number of records should be 0

  Scenario: Import Lead
    Given I download "Lead" Data Template file
    And I fill template with data:
      | Lead name | Status Id | Owner Username | Organization Name | Customer Account Account name | Contact First name | Contact Last name |
      | Jary      | new       | admin          | OroCRM            | Jary Carter                   | Jary               | Carter            |
    When I import file
    And I reload the page
    Then there are 1 records in grid

  Scenario: Convert to opportunity
    Given I click "view" on row "Jary" in grid
    And I click "Convert to Opportunity"
    When I fill form with:
      | Account    | Charlie            |
      | First name | Jary               |
      | Emails     | [jary@example.com] |
    And click "Save and Close"
    Then I should see "Opportunity saved" flash message
    Then I should be on "Jary" Opportunity View page
