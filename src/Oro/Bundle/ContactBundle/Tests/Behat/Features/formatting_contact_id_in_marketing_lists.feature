@regression
@ticket-BB-17267
@fixture-OroContactBundle:contacts_with_big_ids.yml

Feature: Formatting contact id in marketing lists
  In order to manage marketing lists with contacts
  As an Administrator
  I should be able to see correct formatting data in marketing list with contacts in grid and exported csv file

  Scenario: Check contact with correct formatting id in marketing list with contacts in grid
    Given I login as administrator
    And I go to Marketing/Marketing Lists
    When I click view "Contact Marketing List" in grid
    And I should see following grid:
      | Id     | Contact Email           |
      | 1001   | test-1001@example.com   |
      | 100500 | test-100500@example.com |

  Scenario: Export marketing list grid with correct id formatting
    Given I click "Export Grid"
    And I click "CSV"
    Then I should see "Export started successfully. You will receive email notification upon completion." flash message
    And Email should contains the following "Grid export performed successfully. Download" text
    And exported file contains at least the following columns:
      | Id     | Contact Email           |
      | 1001   | test-1001@example.com   |
      | 100500 | test-100500@example.com |
