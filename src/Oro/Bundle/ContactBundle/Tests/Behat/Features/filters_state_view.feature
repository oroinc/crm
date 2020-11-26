@fixture-OroContactBundle:contact.yml

Feature: Filters state view
  In order using grids
  As a Sales rep
  I want to hide filters on grids and boards so they don't take screen space when unnecessary.

  # Description
  # When user hides filters with button in grid toolbar he should see filters state representation
  # After click on it filters have to be shown again

  Scenario: Text representation of filter state
    Given I login as administrator
    And I go to Customers/Contacts
    When I filter First name as Contains "John"
    And I filter Last Name as Contains "Barrows"
    And I click "GridFiltersButton"
    Then I should not see an "GridFilters" element
    Then I should see an "GridFiltersState" element
    And I should see "First name contains \"John\", Last name contains \"Barrows\""
    When I click "GridFiltersState"
    Then I should not see an "GridFiltersState" element
    And I should see an "GridFilters" element
