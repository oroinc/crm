@ticket-BAP-14322
Feature: Unknown Entity namespace alias 'OroSegmentBundle' during import
  ToDo: BAP-16103 Add missing descriptions to the Behat features

  Scenario: Import contacts
    Given I login as administrator
      And I go to Customers/ Contacts
      And there is no records in grid
      And I download "Contact" Data Template file
      And I fill template with data:
        | First Name | Last Name | Emails 1 Email            | Phones 1 Phone | Source Name  | Addresses 1 Country ISO2 code | Addresses 1 State    | Addresses 1 Zip\/Postal Code |
        | Jerry1     | Coleman1  | JerryAColeman@armyspy.com | 585-255-2323   | website      | US                            | New York             | 146084????5??                |
    When I import file
    Then Email should contains the following "Errors: 0 processed: 1, read: 1, added: 1, updated: 0, replaced: 0" text
      And reload the page
      And I should see "JerryAColeman@armyspy.com" in grid with following data:
      |First Name     |Jerry1                   |
      |Last Name      |Coleman1                 |
      |Email          |JerryAColeman@armyspy.com|
      |Phone          |585-255-2323             |
      |Source         |Website                  |
      |Country        |United States            |
      |State          |New York                 |
      |Zip/Postal Code|146084????5??            |
      And number of records should be 1
