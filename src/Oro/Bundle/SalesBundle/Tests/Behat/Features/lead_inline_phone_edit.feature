@ticket-BAP-20560
@fixture-OroSalesBundle:LeadFixture.yml

Feature: Lead Inline Phone Edit
  In order to manage lead feature
  as a user
  I should be able to change leads phone number at the grid

  Scenario: Inline edit lead phone number
    Given I login as administrator
    And I go to Sales/Leads
    Then should see following grid:
      | Lead name |
      | Lead 1    |
    When I edit first record from grid:
      | Phone Number | 123456 |
    Then I should see following grid:
      | Lead name | Phone number |
      | Lead 1    | 123456       |
    When I edit first record from grid:
      | Phone Number | 987654321 |
    Then I should see following grid:
      | Lead name | Phone number |
      | Lead 1    | 987654321    |
