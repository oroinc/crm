@ticket-CRM-9055
@ticket-BAP-18600
@fixture-OroSalesBundle:OpportunityFixture.yml
@fixture-OroSalesBundle:OpportunityTagsFixture.yml
Feature: Create segment with Dictionary field
  In order to manage segments
  As an administrator
  I need to be able to create segment with Dictionary field

  Scenario: Created segment with Dictionary field and "is not empty" filter
    Given I login as administrator
    And go to Sales/Opportunities
    And click Edit "Opportunity 1" in grid
    And I fill form with:
      | Close Reason | Cancelled |
    And I save and close form
    And I should see "Opportunity saved" flash message
    When I go to Reports & Segments / Manage Segments
    And I click "Create Segment"
    And I fill "Segment Form" with:
      | Name         | Test Dictionary field in Segment (is not empty) |
      | Entity       | Opportunity                                     |
      | Segment Type | Manual                                          |
    And I add the following columns:
      | Opportunity name | None |
      | Close reason     | None |
      | Tags             | None |
    And I add the following filters:
      | Field Condition | Tags         | is not empty |
      | Field Condition | Close reason | is not empty |
    And I save and close form
    Then I should see "Segment saved" flash message
    And number of records should be 1
    And I should see following grid:
      | Opportunity name | Close reason | Tags      |
      | Opportunity 1    | Cancelled    | tag1 tag2 |

  Scenario: Created segment with Dictionary field and "is empty tags" filter
    Given I go to Reports & Segments / Manage Segments
    When I click "Create Segment"
    And I fill "Segment Form" with:
      | Name         | Test Dictionary field in Segment (is empty tags) |
      | Entity       | Opportunity                                      |
      | Segment Type | Manual                                           |
    And I add the following columns:
      | Opportunity name | None |
      | Close reason     | None |
      | Tags             | None |
    And I add the following filters:
      | Field Condition | Tags | is empty |
    And I save and close form
    Then I should see "Segment saved" flash message
    And number of records should be 0

  Scenario: Created segment with Dictionary field and "is empty close reason" filter
    Given I go to Reports & Segments / Manage Segments
    When I click "Create Segment"
    And I fill "Segment Form" with:
      | Name         | Test Dictionary field in Segment (is empty close reason) |
      | Entity       | Opportunity                                              |
      | Segment Type | Manual                                                   |
    And I add the following columns:
      | Opportunity name | None |
      | Close reason     | None |
      | Tags             | None |
    And I add the following filters:
      | Field Condition | Close reason | is empty |
    And I save and close form
    Then I should see "Segment saved" flash message
    And number of records should be 0
