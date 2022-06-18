@regression
@ticket-BAP-21453
@fixture-OroSalesBundle:OpportunityFixture.yml

Feature: Opportunity Search
  In order to manage opportunity feature
  As an Administrator
  I should be able to search or not opportunities based on feature state

  Scenario: Search opportunities when the feature enabled
    Given I login as administrator
    When I click "Search"
    And I select "Opportunity" from search types
    And type "Opportunity" in "search"
    Then I should see 1 search suggestion
    When I click "Search Submit"
    Then I should be on Search Result page
    And I should see following search entity types:
      | Type          | N | isSelected |
      | All           | 1 |            |
      | Opportunities | 1 | yes        |
    And I should see following search results:
      | Title         | Type        |
      | Opportunity 1 | Opportunity |

  Scenario: Search opportunities when the feature disabled
    Given I disable Opportunity feature
    And I login as administrator
    When I click "Search"
    When type "Opportunity" in "search"
    Then I should see 0 search suggestion
    When I click "Search Submit"
    Then I should see "No results were found to match your search."
    When I click "Search"
    Then I should not see "Opportunity" in search types
