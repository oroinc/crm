@fixture-OroContactBundle:contacts.yml
@fixture-OroUserBundle:users.yml

Feature: Share contact with users
  In order to have ability work with contacts
  As administrator
  I need to have ability share contact with other users

Scenario: Share contacts
  Given I login as administrator
  And I go to Customers/Contacts
  And I click view "Aadi AABERG" in grid
  And I click "Share"
  And I fill "Share With Form" with:
    | Share with | Charlie Sheen |
  And press select entity button on "Share with" field
  And I select following records in "Select Share With Grid" grid:
    | Megan Fox |
  And I click "Add"
  And I click "Apply"
  And I reload the page
  And I click "Share"
  And I should see following records in "Shared Grid":
    | Charlie Sheen |
    | Megan Fox |
