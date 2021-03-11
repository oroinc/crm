@regression
@ticket-BAP-20425
@fixture-OroContactBundle:LoadContactEntitiesFixture.yml
@fixture-OroAccountBundle:LoadAccountEntitiesFixture.yml
Feature: Check date filters for the Contacts grid
  In order to find contacts by date-related fields
  As an Administrator
  I should be able to filter contact by date and date time columns

  Scenario: Login to backoffice
    Given I login as administrator

  Scenario Outline: Set timezone and check date filters
    When I go to System / Configuration
    And I follow "System Configuration/General Setup/Localization" on configuration sidebar
    And uncheck "Use default" for "Timezone" field
    And I fill form with:
      | Timezone | <timezone> |
    And I save form
    Then I should see "Configuration saved" flash message

    When I go to Customers/ Contacts
    Then there are 2 records in grid
    When I show column Birthday in "Contacts Grid"
    And I show filter "Birthday" in "Contacts Grid" grid
    Then I should see following grid containing rows:
      | Email          | Birthday   |
      | test1@test.com | 2000-01-01 |
      | test2@test.com | 2000-01-02 |

    When I filter Birthday as equals "2000-01-01" as single value
    Then number of records should be 1
    And I should see following grid containing rows:
      | Email          | Birthday   |
      | test1@test.com | 2000-01-01 |

    When I filter Birthday as between "2000-01-02" and "2000-01-02"
    Then number of records should be 1
    And I should see following grid containing rows:
      | Email          | Birthday   |
      | test2@test.com | 2000-01-02 |

    When I filter Birthday as between "2000-01-01" and "2000-01-02"
    Then number of records should be 2
    And I should see following grid containing rows:
      | Email          | Birthday   |
      | test1@test.com | 2000-01-01 |
      | test2@test.com | 2000-01-02 |

    Examples:
      | timezone        |
      | Other/UTC       |
      | Indian/Maldives |
