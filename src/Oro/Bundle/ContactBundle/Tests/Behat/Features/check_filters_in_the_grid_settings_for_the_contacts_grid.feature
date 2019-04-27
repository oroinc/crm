@regression
@ticket-@BB-16554
@fixture-OroContactBundle:LoadContactEntitiesFixture.yml
@fixture-OroAccountBundle:LoadAccountEntitiesFixture.yml
Feature: Check filters in the Grid settings for the Contacts grid
  In order to operate with contacts grid filters
  As an Administrator
  I should see appropriate list of filters in filter manager

  Scenario: Check available filters on contacts grid
    Given I login as administrator
    And I go to Customers/ Contacts
    When click "Grid Settings"
    And I click "Filters" tab
    Then I should see following filters in the grid settings in exact order:
      | First Name                                |
      | Last Name                                 |
      | Birthday                                  |
      | Gender                                    |
      | Email                                     |
      | Phone                                     |
      | Fax                                       |
      | Skype                                     |
      | Twitter                                   |
      | Facebook                                  |
      | LinkedIn                                  |
      | Google+                                   |
      | Source                                    |
      | Country                                   |
      | State                                     |
      | Zip/Postal Code                           |
      | City                                      |
      | Street                                    |
      | Created At                                |
      | Updated At                                |
      | Picture                                   |
      | Owner                                     |
      | Assigned To                               |
      | Reports To                                |
      | Business Unit                             |
      | Total Times Contacted                     |
      | Total Number Of Incoming Contact Attempts |
      | Total Number Of Outgoing Contact Attempts |
      | Last Contact Datetime                     |
      | Last Incoming Contact Datetime            |
      | Last Outgoing Contact Datetime            |
      | Days Since The Last Contact               |
      | Tags                                      |
