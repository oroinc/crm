@fixture-OroAccountBundle:crud-account.yml
@ticket-BAP-15992
Feature: CRUD Account
  In order to have ability create, view, update and delete accounts
  As a administrator
  I need have form, grid and actions for Account entity

Scenario: Create new Account
  Given I login as administrator
  And the following contacts:
    | First Name | Last Name | Email     |
    | Joan       | Anderson  | <email()> |
    | Craig      | Bishop    | <email()> |
    | Jean       | Castillo  | <email()> |
    | Willie     | Chavez    | <email()> |
    | Arthur     | Fisher    | <email()> |
    | Wanda      | Ford      | <email()> |
  And I go to Customers/Accounts
  And I click "Create Account"
  And I fill "Account Form" with:
    | Account Name | Good Company    |
    | Description  | Our new partner |
    | Owner        | Harry           |
  And I click "Add"
  And check Joan Anderson and Wanda Ford in grid
  When I click "Select"
  Then two contacts added to form
  And I select Wanda contact as default
  When I save form
  And I should see "Account saved" flash message
  And Account Name field should has Good Company value
  And Description field should has Our new partner value
  And I save and close form
  And I should see two contacts
  And Wanda should be default contact
  And Harry Freeman should be an owner

Scenario: Edit Account
  Given I'm edit entity
  And press select entity button on Owner field
  Then click on Todd Greene in grid
  And I fill "Account Form" with:
    | Account Name | Oro Inc         |
    | Description  | Our old partner |
  And select Joan contact as default
  And delete Wanda contact
  When I save and close form
  And I should see "Account saved" flash message
  And Account Name field should has Oro Inc value
  And Description field should has Our old partner value
  And I should see one contact
  And Joan should be default contact
  And Todd Greene should be an owner

Scenario: Delete Account
  And click "Delete" in "PageActionButtonsContainer" element
  When confirm deletion
  Then I should see "item deleted" flash message
  And there is no records in grid

Scenario: Search for owner by email when create Account
  Given I login as administrator
  And I go to Customers/Accounts
  And I click "Create Account"
  When I type "email" in "Owner"
  Then I should see "test.email1@example.com"
  And I should see "test.email2@example.com"
