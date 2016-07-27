Feature: Contacts CRUD
  In order to have ability work with contacts
  As administrator
  I need to have ability create, view, update and delete contact entity

Background:
  Given I login as "admin" user with "admin" password

Scenario: Required fields
  Given I go to Customers/Contacts
  And press "Create Contact"
  When save and close form
  Then I should see "At least one of the fields First name, Last name, Emails or Phones must be defined." error message

Scenario: Create contact
  Given there are following accounts:
    | Name               | Owner  | Organization  |
    | Warner Brothers    | @admin | @organization |
    | Columbia Pictures  | @admin | @organization |
    | Paramount Pictures | @admin | @organization |
  And I go to Customers/Contacts
  And press "Create Contact"
  And fill form with:
    | First name | Charlie                                |
    | Last name  | Sheen                                  |
    | Emails     | [charlie@gmail.com, sheen@charlie.com] |
    | Phones     | [+1 415-731-9375, +1 415-656-4418]     |
    | Twitter    | charliesheen                           |
    | Facebook   | CharlieSheen                           |
    | LinkedIn   | charlie-sheen-74755931                 |
    | Google+    | 111536551725236448567                  |
  And set "charlie@gmail.com" as primary email
  And set "+1 415-731-9375" as primary phone
  And check Warner Brothers and Columbia Pictures in grid
  And I fill in address:
    | Primary         | check         |
    | Country         | United States |
    | Street          | Selma Ave     |
    | City            | Los Angeles   |
    | Zip/postal code | 90028         |
    | State           | California    |
  And add new address with:
    | Primary         | check               |
    | Country         | Ukraine             |
    | Street          | Myronosytska 57     |
    | City            | Kharkiv             |
    | Zip/postal code | 61000               |
    | State           | Kharkivs'ka Oblast' |
  When save and close form
  Then I should see "Contact saved" flash message
  And Phone "+1 415-731-9375" should be primary
  And email "charlie@gmail.com" should be primary
  And avatar should be default avatar
  And Warner Brothers and Columbia Pictures should be set as accounts
  And should see next social links:
    | Twitter    | https://twitter.com/charliesheen                  |
    | Facebook   | https://www.facebook.com/CharlieSheen             |
    | Google+    | https://profiles.google.com/111536551725236448567 |
    | LinkedIn   | http://www.linkedin.com/in/charlie-sheen-74755931 |
  And two addresses should be in page
  And Ukraine address must be primary

Scenario: Cancel edit
  Given I go to Customers/Contacts
  And I click edit Charlie Sheen in grid
  And I delete all addresses
  And fill form with:
    | First name | No name          |
    | Last name  | No name          |
    | Emails     | [fake@gmail.com] |
    | Phones     | [+0000000000000] |
  When press "Cancel"
  Then I should see Charlie Sheen in grid with following data:
    | Email   | charlie@gmail.com   |
    | Phone   | +1 415-731-9375     |
    | Country | Ukraine             |
    | State   | Kharkivs'ka Oblast' |

Scenario: Change primary address
  Given I go to Customers/Contacts
  And I click view Charlie Sheen in grid
  And click edit LOS ANGELES address
  And check "Primary"
  When I press "Save"
  Then LOS ANGELES address must be primary
  And contact has 2 addresses

Scenario: Add/Delete address from contact view
  Given I go to Customers/Contacts
  And I click view Charlie Sheen in grid
  When I delete Ukraine address
  Then contact has one address
  When I press "+ Add Address"
  And fill form with:
    | Country         | Germany       |
    | Street          | Mühlendamm 78 |
    | City            | Hamburg       |
    | Zip/postal code | 22087         |
    | State           | Hamburg       |
  And press "Save"
  Then contact has 2 addresses
  And LOS ANGELES address must be primary

Scenario: Edit contact
  Given I go to Customers/Contacts
  And I click edit Charlie Sheen in grid
  And fill form with:
    | First name | Charlie2          |
    | Last name  | Sheen2            |
    | Picture    | charlie-sheen.jpg |
  And set "sheen@charlie.com" as primary email
  And set "+1 415-656-4418" as primary phone
  When I save and close form
  Then I should see "Contact saved" flash message
  And I should see "Charlie2 Sheen2"
  And Phone "+1 415-656-4418" should be primary
  And email "sheen@charlie.com" should be primary
  And avatar should be "charlie-sheen.jpg"

Scenario: Delete contact
  Given I go to Customers/Contacts
  And I click view Charlie2 Sheen2 in grid
  When I press "Delete Contact"
  And confirm deletion
  Then I should see "Contact deleted" flash message
  And there is no records in grid
