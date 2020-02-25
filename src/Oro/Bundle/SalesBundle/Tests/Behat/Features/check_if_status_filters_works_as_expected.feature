@fixture-OroSalesBundle:OpportunityFixture.yml
@ticket-BAP-14981
Feature: Check if status filters works as expected
  In order to check opportunities
  As a Sales rep
  I want to use status filters

  Scenario: Click status filter multiple times and check
    Given I login as administrator
    And I go to Sales/Opportunities
    And I click "Filter Toggle"
    And I click "OpportunityStatusFilter"
    And I click "OpportunityStatusFilter"
    And I click "OpportunityStatusFilter"
    Then I should see 1 elements "OpportunityStatusFilerSelectButton"
    And I should see 1 elements "OpportunityStatusFilerSelectField"
