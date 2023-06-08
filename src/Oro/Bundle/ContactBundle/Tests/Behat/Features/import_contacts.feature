@ticket-BAP-16465
@ticket-BB-21883
@ticket-CRM-9400

Feature: Import Contacts
  In order to add multiple contacts at once
  As an Administrator
  I want to be able to import contacts from a CSV file using a provided template

  Scenario: Change configuration of Contact`s Name prefix field
    Given I login as administrator
    And I go to System/ Entities/ Entity Management
    And I filter Name as is equal to "Contact"
    And I click view Contact in grid
    And I click edit Name prefix in grid
    When I fill form with:
      | Use as Identity Field | Only when not empty |
    And I save and close form
    Then I should see "Field saved" flash message

  Scenario: Use as Identity Field value could not be changed for ID field
    When I click edit id in grid
    Then I should see an "Disabled Use as Identity Field" element
    And Use as Identity Field field should has "Always" value

  Scenario: Data Template for Contacts
    Given I go to Customers/ Contacts
    When I download "Contacts" Data Template file
    Then I see the following columns in the downloaded csv template:
      | Id                                      |
      | Name prefix                             |
      | First name                              |
      | Last name                               |
      | Gender                                  |
      | Birthday                                |
      | Source Name                             |
      | Owner Username                          |
      | Assigned to Username                    |
      | Emails 1 Email                          |
      | Phones 1 Phone                          |
      | Groups 1 Label                          |
      | Accounts 1 Account name                 |
      | Accounts Default Contact 1 Account name |
      | Addresses 1 Label                       |
      | Addresses 1 First name                  |
      | Addresses 1 Last name                   |
      | Addresses 1 Street                      |
      | Addresses 1 Zip/Postal Code             |
      | Addresses 1 City                        |
      | Addresses 1 State Combined code         |
      | Addresses 1 Country ISO2 code           |
      | Organization Name                       |
      | Picture URI                             |
      | Picture UUID                            |

  Scenario: Import new Contacts
    Given I copy contact fixture "charlie-sheen.jpg" to import upload dir
    And I remember number of files in attachment directory
    And I fill template with data:
      | Id | Name prefix | First name | Last name | Gender | Birthday   | Source Name | Owner Username      | Assigned to Username | Emails 1 Email              | Phones 1 Phone | Groups 1 Label           | Accounts 1 Account name | Accounts Default Contact 1 Account name | Addresses 1 Label | Addresses 1 First name | Addresses 1 Last name | Addresses 1 Street  | Addresses 1 Zip\/Postal Code | Addresses 1 City | Addresses 1 State Combined code | Addresses 1 Country ISO2 code | Organization Name | Picture URI       | Picture UUID |
      |    | Mr.         | Roy        | Greenwell | male   | 01/18/1968 | website     | austin.rivers_3d974 | austin.rivers_3d974  | RoyLGreenwell@superrito.com | 765-538-2134   | Demographic Segmentation | Big D Supermarkets      | Big D Supermarkets                      | Primary Address   | Roy                    | Greenwell             | 2413 Capitol Avenue | 47981                        | Romney           | US-IN                           | US                            | ORO               | charlie-sheen.jpg |              |
    When I import file
    Then Email should contains the following "Errors: 5 processed: 1, read: 1, added: 1, updated: 0, replaced: 0" text
    And number of files in attachment directory is 1 more than remembered

  Scenario: Check contacts
    When I reload the page
    And number of records should be 1
    And I should see following grid:
      | First name | Last name | Email                       | Phone        | Source  | Country       | State   | Zip/Postal Code |
      | Roy        | Greenwell | RoyLGreenwell@superrito.com | 765-538-2134 | Website | United States | Indiana | 47981           |
    When I click view Greenwell in grid
    Then avatar should not be default avatar

  Scenario: Export contacts
    Given I go to Customers/ Contacts
    When I run export
    Then exported file contains at least the following columns:
      | Id | Name prefix | First name | Middle name | Last name | Name suffix | Gender | Description | Job Title | Fax | Skype | Twitter | Facebook | Google+ | LinkedIn | Birthday   | Source Name | Contact Method Name | Emails 1 Email              | Phones 1 Phone | Accounts 1 Account name | Accounts Default Contact 1 Account name | Addresses 1 Label | Addresses 1 Organization | Addresses 1 Name prefix | Addresses 1 First name | Addresses 1 Middle name | Addresses 1 Last name | Addresses 1 Name suffix | Addresses 1 Street  | Addresses 1 Street 2 | Addresses 1 Zip/Postal Code | Addresses 1 City | Addresses 1 State | Addresses 1 State Combined code | Addresses 1 Country ISO2 code | Organization Name | Picture URI                       | Picture UUID | Tags |
      | 1  | Mr.         | Roy        |             | Greenwell |             | male   |             |           |     |       |         |          |         |          | 01/18/1968 | website     |                     | RoyLGreenwell@superrito.com | 765-538-2134   | Big D Supermarkets      | Big D Supermarkets                      | Primary Address   |                          |                         | Roy                    |                         | Greenwell             |                         | 2413 Capitol Avenue |                      | 47981                       | Romney           |                   | US-IN                           | US                            | ORO               | <contains("attachment/download")> | <notEmpty()> |      |

  Scenario: Check account contact
    Given I go to Customers/ Accounts
    When I click view Greenwell in grid
    Then I should see 1 contact

  Scenario: Import new Contacts with Add strategy
    Given I go to Customers/ Contacts
    When I fill template with data:
      | Id | Name prefix | First name | Last name | Gender | Birthday   | Source Name | Owner Username      | Assigned to Username | Emails 1 Email              | Phones 1 Phone | Groups 1 Label           | Accounts 1 Account name |
      |    | Mr.         | Roy        | Greenwell | male   | 01/18/1968 | website     | austin.rivers_3d974 | austin.rivers_3d974  | RoyLGreenwell@superrito.com | 765-538-2134   | Demographic Segmentation | Big D Supermarkets      |
    When I import file with strategy "Add"
    Then Email should contains the following "Errors: 0 processed: 1, read: 1, added: 1, updated: 0, replaced: 0" text

  Scenario: Validate contacts with with Add strategy
    Given I login as administrator
    Given I go to Customers/ Contacts
    And I fill import file with data:
      | Id | Name prefix | First name | Middle name | Last name | Name suffix | Gender | Description      | Job Title | Fax          | Skype            | Twitter                              | Facebook                                  | Google+                                 | LinkedIn                                    | Birthday   | Source Name | Contact Method Name | Owner Username | Assigned to Username | Emails 1 Email            | Emails 2 Email         | Emails 3 Email            | Phones 1 Phone | Phones 2 Phone | Phones 3 Phone | Groups 1 Label | Groups 2 Label    | Accounts Default Contact 1 Account name | Accounts 1 Account name | Accounts 2 Account name | Addresses 1 Label | Addresses 1 Organization | Addresses 1 Name prefix | Addresses 1 First name | Addresses 1 Middle name | Addresses 1 Last name | Addresses 1 Name suffix | Addresses 1 Street   | Addresses 1 Street 2 | Addresses 1 Zip/Postal Code | Addresses 1 City | Addresses 1 State | Addresses 1 State Combined code | Addresses 1 Country ISO2 code | Addresses 1 Types 1 Name | Addresses 1 Types 2 Name | Addresses 2 Label | Addresses 2 Organization | Addresses 2 Name prefix | Addresses 2 First name | Addresses 2 Middle name | Addresses 2 Last name | Addresses 2 Name suffix | Addresses 2 Street   | Addresses 2 Street 2 | Addresses 2 Zip/Postal Code | Addresses 2 City | Addresses 2 State | Addresses 2 State Combined code | Addresses 2 Country ISO2 code | Addresses 2 Types 1 Name | Addresses 2 Types 2 Name | Addresses 3 Label | Addresses 3 Organization | Addresses 3 Name prefix | Addresses 3 First name | Addresses 3 Middle name | Addresses 3 Last name | Addresses 3 Name suffix | Addresses 3 Street    | Addresses 3 Street 2 | Addresses 3 Zip/Postal Code | Addresses 3 City | Addresses 3 State | Addresses 3 State Combined code | Addresses 3 Country ISO2 code | Addresses 3 Types 1 Name | Addresses 3 Types 2 Name | Organization Name | Picture URI | Picture UUID | Tags                     |
      | 1  | Mr.         | Jerry      |             | Coleman   | Jr.         | male   | "Sample Contact" | Manager   | 713-450-0721 | crm-jerrycoleman | https://twitter.com/crm-jerrycoleman | https://www.facebook.com/crm-jerrycoleman | https://plus.google.com/454646545646546 | http://www.linkedin.com/in/crm-jerrycoleman | 03/07/1973 | website     | phone               | admin          | admin                | JerryAColeman@armyspy.com | JerryAColeman@cuvox.de | JerryAColeman@teleworm.us | 585-255-1127   | 914-412-0298   | 310-430-7876   | "Sales Group"  | "Marketing Group" | Coleman                                 | Coleman                 | Smith                   |                   |                          |                         | Jerry                  |                         | Coleman               |                         | "1215 Caldwell Road" |                      | 14608                       | Rochester        |                   | US-NY                           | US                            | billing                  | shipping                 |                   |                          |                         | Jerry                  |                         | Coleman               |                         | "4677 Pallet Street" |                      | 10011                       | "New York"       |                   | US-NY                           | US                            |                          |                          |                   |                          |                         | Jerry                  |                         | Coleman               |                         | "52 Jarvisville Road" |                      | 11590                       | "New York"       |                   | US-NY                           | US                            |                          |                          | ORO               |             |              | "custom tag, second tag" |
    When I validate file with strategy "Add"
    Then Email should contains the following "Errors: 0 processed: 1, read: 1" text

  Scenario: Check account contact after second import
    Given I go to Customers/ Accounts
    When I click view Greenwell in grid
    Then I should see 2 contacts

