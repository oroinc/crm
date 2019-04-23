@ticket-BB-16463
Feature: Submit contact us form on storefront

  Scenario: Create contact us request
    Given I am on homepage
    When I click "Contact Us"
    And I fill "Contact Request Form" with:
      | First Name        | First Name       |
      | Last Name         | Last Name        |
      | Organization Name | New Organization |
      | Phone             | 123456789        |
      | Email             | test@email.com   |
      | Comment           | <h1>note</h1>    |
    And I click "Submit"
    Then I should see "Thank you for your Request!" flash message

    When I login as administrator
    And I go to Activities/Contact Requests
    And I click view test@email.com in grid
    Then I should see Contact Request with:
      | First Name        | First Name       |
      | Last Name         | Last Name        |
      | Organization Name | New Organization |
      | Phone             | 123456789        |
      | Email             | test@email.com   |
      | Comment           | <h1>note</h1>    |
