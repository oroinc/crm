@ticket-BAP-17563
@fixture-OroAccountBundle:Accounts.yml
Feature: Warning on edit form with unsaved data
  In order to warning users about unsaved data on edit form
  As a administrator
  I want to be sure that there is such type of warning when living form with unsaved data

  Scenario: Warning on edit form with unsaved data when changing the page
    Given I login as administrator
    And go to Customers/ Accounts
    When click edit "Account1" in grid
    And fill form with:
      | Account name | NewAccount1     |
      | Description  | testDescription |
    And go to Customers/ Accounts
    Then I should see alert with message "You have unsaved changes, are you sure you want to leave this page?"
    And I accept alert

  Scenario: Warning on edit form with unsaved data when use pagination controls to navigate to previous/next record
    Given click edit "Account2" in grid
    And fill form with:
      | Account name | NewAccount2     |
      | Description  | testDescription |
    When click "Next Paginator Button"
    Then should see "You have unsaved changes. Do you want to save them before leaving the page?" in confirmation dialogue
    And click "Save" in confirmation dialogue
    And should see "Account saved" flash message
    And should see "Accounts / Account3"
    When fill form with:
      | Account name | NewAccount3     |
      | Description  | testDescription |
    And click "Previous Paginator Button"
    Then should see "You have unsaved changes. Do you want to save them before leaving the page?" in confirmation dialogue
    And click "Discard" in confirmation dialogue
    And should not see "Account saved" flash message
    And should see "Accounts / NewAccount2"
    When go to Customers/ Accounts
    Then should see following grid:
      | Account name |
      | Account1     |
      | Account3     |
      | NewAccount2  |
    And click edit "Account3" in grid
    And fill form with:
      | Account name | NewAccount3     |
      | Description  | testDescription |
    When click "Previous Paginator Button"
    Then should see "You have unsaved changes. Do you want to save them before leaving the page?" in confirmation dialogue
    And click "Cancel" in confirmation dialogue
    And should see "Accounts / Account3"
