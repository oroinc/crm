@regression
@ticket-BB-16554
@ticket-BAP-22067
@fixture-OroContactBundle:LoadContactEntitiesFixture.yml
@fixture-OroAccountBundle:LoadAccountEntitiesFixture.yml
@fixture-OroUserBundle:AdditionalUsersFixture.yml

Feature: Check filters in the Grid settings for the Contacts grid
  In order to operate with contacts grid filters
  As an Administrator
  I should see appropriate list of filters in filter manager. Besides we check assigned to filter because it's the first
  choice tree filter element without renderable.

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

  Scenario: Check assigned to filters on contacts grid
    When I go to Customers/ Contacts
    Then there are 2 records in grid
    When I show column Assigned to in "Contacts Grid"
    And I show filter "Assigned to" in "Contacts Grid" grid
    Then I should see following grid containing rows:
      | Email          | Assigned to |
      | test1@test.com |             |
      | test2@test.com | John Doe    |
    When I choose "John Doe" in the Assigned to filter
    Then I should see following grid containing rows:
      | Email          | Assigned to |
      | test2@test.com | John Doe    |
