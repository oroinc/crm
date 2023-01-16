Feature: Contacts CRUD
  In order to have ability work with contacts
  As administrator
  I need to have ability create, view, update and delete contact entity

Scenario: Required fields
  Given I login as administrator
  And there are following accounts:
    | Name               | Owner  | Organization  |
    | Warner Brothers    | @admin | @organization |
    | Columbia Pictures  | @admin | @organization |
    | Paramount Pictures | @admin | @organization |
  And I go to Customers/Contacts
  And click "Create Contact"
  When save and close form
  Then I should see "At least one of the fields First name, Last name, Emails or Phones must be defined." error message

Scenario: Create contact
  Given I fill form with:
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
    | Zip/Postal Code | 90028         |
    | State           | California    |
  And add new address with:
    | Primary         | check               |
    | Country         | Ukraine             |
    | Street          | Myronosytska 57     |
    | City            | Kharkiv             |
    | Zip/Postal Code | 61000               |
    | State           | Kharkivs'ka Oblast' |
  When save and close form
  Then I should see "Contact saved" flash message

Scenario: Assert values at Contact view page
  Then Phone "+1 415-731-9375" should be primary
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
  Given I'm edit entity
  And I delete all "addresses"
  And fill form with:
    | First name | No name          |
    | Last name  | No name          |
    | Emails     | [fake@gmail.com] |
    | Phones     | [+0000000000000] |
  When click "Cancel"
  Then I should see Charlie Sheen in grid with following data:
    | Email   | charlie@gmail.com   |
    | Phone   | +1 415-731-9375     |
    | Country | Ukraine             |
    | State   | Kharkivs'ka Oblast' |

Scenario: Change primary address
  And I click view Charlie Sheen in grid
  And click edit LOS ANGELES address
  And check "Primary"
  When I click "Save"
  Then LOS ANGELES address must be primary
  And contact has 2 addresses

Scenario: Add/Delete address from contact view
  When I delete Ukraine address
  And click "Yes, Delete"
  Then contact has one address
  When I click "Add Address"
  And fill form with:
    | Country         | Germany       |
    | Street          | Mühlendamm 78 |
    | City            | Hamburg       |
    | Zip/Postal Code | 22087         |
    | State           | Hamburg       |
  And click "Save"
  Then contact has 2 addresses
  And LOS ANGELES address must be primary

Scenario: Edit contact
  Given I'm edit entity
  And fill form with:
    | First name | Charlie2          |
    | Last name  | Sheen2            |
    | Picture    | charlie-sheen.jpg |
  And set "sheen@charlie.com" as primary email
  And set "+1 415-656-4418" as primary phone
  When I save and close form
  Then I should see "Contact saved" flash message

Scenario: Assert field values of Contact view page after edit
  Then I should see "Charlie2 Sheen2"
  And Phone "+1 415-656-4418" should be primary
  And email "sheen@charlie.com" should be primary
  And avatar should not be default avatar
  And page has "Charlie2 Sheen2" header

Scenario: Delete contact
  Given I remember number of files in attachment directory
  When I click "Delete Contact"
  And confirm deletion
  Then I should see "Contact deleted" flash message
  And there is no records in grid
  And number of files in attachment directory is 1 less than remembered

Scenario: Validate Social links length
  Given I login as administrator
  Then I go to Customers/Contacts
  Then I click "Create Contact"
  Then I fill form with:
    | First name | Charlie                                |
    | Last name  | Sheen                                  |
    | Phones     | [+1 415-731-9375, +1 415-656-4418]     |
    | Twitter    | http://twitter.com/test_acc?semper=luctus&est=et&quam=ultrices&pharetra=posuere&magna=cubilia&ac=curae&consequat=nulla&metus=dapibus&sapien=dolor&ut=vel&nunc=est&vestibulum=donec&ante=odio&ipsum=justo&primis=sollicitudin&in=ut&faucibus=suscipit&orci=auhuiuhywef |
    | Skype      | http://skype.com/test_acc?semper=luctus&est=et&quam=ultrices&pharetra=posuere&magna=cubilia&ac=curae&consequat=nulla&metus=dapibus&sapien=dolor&ut=vel&nunc=est&vestibulum=donec&ante=odio&ipsum=justo&primis=sollicitudin&in=ut&faucibus=suscipit&orci=auhuiuhywdwwe |
    | Facebook   | http://facebook.com/test_acc?semper=luctus&est=et&quam=ultrices&pharetra=posuere&magna=cubilia&ac=curae&consequat=nulla&metus=dapibus&sapien=dolor&ut=vel&nunc=est&vestibulum=donec&ante=odio&ipsum=justo&primis=sollicitudin&in=ut&faucibus=suscipit&orci=auhuiuhywf |
    | Google+    | http://plus.google.com/test_acc?semper=luctus&est=et&quam=ultrices&pharetra=posuere&magna=cubilia&ac=curae&consequat=nulla&metus=dapibus&sapien=dolor&ut=vel&nunc=est&vestibulum=donec&ante=odio&ipsum=justo&primis=sollicitudin&in=ut&faucibus=suscipit&orci=auhuiuh |
    | LinkedIn   | http://plus.google.com/test_acc?semper=luctus&est=et&quam=ultrices&pharetra=posuere&magna=cubilia&ac=curae&consequat=nulla&metus=dapibus&sapien=dolor&ut=vel&nunc=est&vestibulum=donec&ante=odio&ipsum=justo&primis=sollicitudin&in=ut&faucibus=suscipit&orci=auhuiuh |
  When I save form
  Then I should see validation errors:
    | Twitter  | This value is too long. It should have 255 characters or less. |
    | Skype    | This value is too long. It should have 255 characters or less. |
    | Facebook | This value is too long. It should have 255 characters or less. |
    | Google+  | This value is too long. It should have 255 characters or less. |
    | LinkedIn | This value is too long. It should have 255 characters or less. |
