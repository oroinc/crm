@fixture-crud-account.yml
Feature: CRUD Account
  In order to have ability create, view, update and delete accounts
  As a administrator
  I need have form, grid and actions for Account entity

Background:
  Given I login as "admin" user with "admin" password

Scenario: Create new Account
  Given the following contacts:
    | First Name | Last Name | Email     |
    | Joan       | Anderson  | <email()> |
    | Craig      | Bishop    | <email()> |
    | Jean       | Castillo  | <email()> |
    | Willie     | Chavez    | <email()> |
    | Arthur     | Fisher    | <email()> |
    | Wanda      | Ford      | <email()> |
  And I open the menu "Customers" and click "Accounts"
  And I press "Create Account"
  And press select arrow in Owner field
  And fill "Ha" in search entity field
  And must see only one result
  Then I press on Harry in search result
  And I fill "Account" form with:
    | Account Name | Good Company    |
    | Description  | Our new partner |
  And I press "Add"
  And check Joan Anderson and Wanda Ford in grid
  When I press "Select"
  Then two contacts added to form
  And I select Wanda contact as default
  When I save and close form
  And I should see "Account saved" flash message
  And Account Name field should be with Good Company value
  And Description field should be with Our new partner value
  And there are should be two contacts
  And Wanda should be default contact
  And Harry Freeman should be an owner

Scenario: Edit Account
  Given I open the menu "Customers" and click "Accounts"
  And click edit Good Company in grid
  And press select entity button on Owner field
  Then click on Todd Greene in grid
  And I fill "Account" form with:
    | Account Name | Oro Inc         |
    | Description  | Our old partner |
  And select Joan contact as default
  And delete Wanda contact
  When I save and close form
  And I should see "Account saved" flash message
  And Account Name field should be with Oro Inc value
  And Description field should be with Our old partner value
  And there is should be one contact
  And Joan should be default contact
  And Todd Greene should be an owner
