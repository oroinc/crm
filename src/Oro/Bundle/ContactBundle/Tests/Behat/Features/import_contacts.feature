@ticket-BAP-16465

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
    Then I see Id column
    And I see Name prefix column
    And I see First name column
    And I see Last name column
    And I see Gender column
    And I see Birthday column
    And I see Source Name column
    And I see Owner Username column
    And I see Assigned to Username column
    And I see Emails 1 Email column
    And I see Phones 1 Phone column
    And I see Groups 1 Label column
    And I see Accounts 1 Account name column
    And I see Accounts Default Contact 1 Account name column
    And I see Addresses 1 Label column
    And I see Addresses 1 First name column
    And I see Addresses 1 Last name column
    And I see Addresses 1 Street column
    And I see Addresses 1 Zip/Postal Code column
    And I see Addresses 1 City column
    And I see Addresses 1 State Combined code column
    And I see Addresses 1 Country ISO2 code column
    And I see Organization Name column

  Scenario: Import new Contacts
    Given I fill template with data:
      | Id | Name prefix | First name | Last name | Gender | Birthday   | Source Name | Owner Username      | Assigned to Username | Emails 1 Email              | Phones 1 Phone | Groups 1 Label           | Accounts 1 Account name | Accounts Default Contact 1 Account name | Addresses 1 Label | Addresses 1 First name | Addresses 1 Last name | Addresses 1 Street  | Addresses 1 Zip\/Postal Code | Addresses 1 City | Addresses 1 State Combined code | Addresses 1 Country ISO2 code | Organization Name |
      |    | Mr.         | Roy        | Greenwell | male   | 01/18/1968 | website     | austin.rivers_3d974 | austin.rivers_3d974  | RoyLGreenwell@superrito.com | 765-538-2134   | Demographic Segmentation | Big D Supermarkets      | Big D Supermarkets                      | Primary Address   | Roy                    | Greenwell             | 2413 Capitol Avenue | 47981                        | Romney           | US-IN                           | US                            | ORO               |
    When I import file
    Then Email should contains the following "Errors: 5 processed: 1, read: 1, added: 1, updated: 0, replaced: 0" text

    When I reload the page
    And number of records should be 1
    And I should see following grid:
      | First name | Last name | Email                       | Phone        | Source  | Country       | State   | Zip/Postal Code |
      | Roy        | Greenwell | RoyLGreenwell@superrito.com | 765-538-2134 | Website | United States | Indiana | 47981           |
