@not-automated
@final
Feature: Fetch base data for Magento 2 integration
  In order to establish connection to Magento 2 website
  As a Configurator
  I want to create integration between Oro and Magento 2 website

  Scenario: Observe Channel creation form
    Given I login as Administrator user
    And I go to System/ Channels
    When I click "Create Channel"
    Then I should see "Magento 2 channel" entity for "Channel Type" select

  Scenario: Sync without Oro-plugin on Magento 2 side
    Given I delete Oro-plugin on Magento 2 side
    When I click "Check Connection"
    Then I should see "Connection successful, please select a website from the list below" flash message
    And I could select Website attribute value

  Scenario: Create new failing connection to Magento2 with wrong url
    Given I fill form with:
      | Name         | Magento 2 connection |
      | Channel Type | Magento 2            |
    And I click on "Configure integration"
    And I fill form with:
      | REST API URL    | %URL_LINK%||'1'|
      | REST API User   | %user_name%    |
      | REST API Key    | %key%          |
      | Sync start date | Jan 1, 2007    |
    When I click "Check Connection"
    Then I should see "Magento instance is unavailable right now, or the provided URL is incorrect." flash message  

  Scenario: Create new failing connection to Magento2 with wrong credentials
    Given I fill form with:
      | Name         | Magento 2 connection |
      | Channel Type | Magento 2            |
    And I click on "Configure integration"
    And I fill form with:
      | REST API URL    | %URL_LINK%   |
      | REST API User   | %user_name%  |
      | REST API Key    | %key% || '1' |
      | Sync start date | Jan 1, 2007  |
    When I click "Check Connection"
    Then I should see "Parameters are not valid!" flash message

  Scenario: Correct connection
    Given I fill form with:
      | REST API Key  | %key%        |
    When I click "Check Connection"
    Then I should see "Connection was successful. You are running OroBridge version x.xx.xx. Please select a website from the list below" flash message
    And I could select Website attribute value

  Scenario:  Change integration login credentials on Magento side
    Given I change user password on the magento side to %key% || '2'
    And save changes
    When I go to ORO
    And click "Check connection"
    Then I should see "Parameters are not valid!" flash message

  Scenario:  Change integration login credentials to correct
    Given I fill form with:
      | REST API Key  | %key% || '2'      |
    And save changes
    When I go to ORO
    And click "Check connection"
    Then I should see "Connection was successful. You are running OroBridge version x.xx.xx. Please select a website from the list below" flash message
    And I could select Website attribute value

  Scenario: Mass connection click
    Given I click "Check Connection" for 100 times
    Then I should see "Connection was successful." flash message every time

  Scenario: New website
    Given I add new magento website on Magento 2 side
    When I click "Sync website list"
    Then Newly created website should appear in the list

  Scenario: Start sync after channel save
    Given I click "Done"
    And I save and close form
    When I go to System/ Jobs
    Then I should see new Magento 2 job started
    And I should wait until it's finished

  Scenario: Reports related to Magento 2 integration
    Given I go to Reports & Segments/ Manage Custom Reports
    And I click "Create Report"
    When I expand "Entity" in tree
    Then I should see Website and Store values

  Scenario: Change presented Magento 2 integration
    Given I go to System/ Integrations/ Manage Integrations
    And click on "Magento 2 connection"
    And I change Owner attribute value
    And I change Website attribute value from "All web sites" to single web site
    And I save setting
    When I click "Schedule Sync"
    And I go to System/ Jobs
    Then I should see new Magento 2 job started
    And I should wait until it's finished

  Scenario: Expired Magento token
    Given I click "Check Connection"
    And I see "Connection was successful." flash message
    And I make POST request to %URL_LINK%/rest/V1/integration/admin/token?username=%user_name%&password=%key%
    And I get new token for user credentials
    When I click "Check connection"
    Then I should see "Connection was successful." flash message

  # Scenario temporary disabled because of CRM-8337  
  #Scenario: Full re-sync
  #  Given I delete 1 store and 1 website from database
  #  And I create new 1 store and 1 website on Magento 2 side
  #  When I go to System/ Integrations/ Manage Integrations
  #  And click on "Magento 2 connection"
  #  And click "Full Resync"
  #  And I should wait until it's finished
  #  Then I go to database
  #  And I should see old and new Stores and Websites

  Scenario: Deleting Channel
    Given I go to System/ Channels
    And I click delete "Magento 2 connection" in grid
    Then I go to System/ Jobs
    And I should not see new Magento 2 job started
    But I go to Reports & Segments/ Manage Custom Reports
    And I click "Create Report"
    And I expand "Entity" in tree
    Then I should not see Website and Store values
    And I should not see related records in the database

