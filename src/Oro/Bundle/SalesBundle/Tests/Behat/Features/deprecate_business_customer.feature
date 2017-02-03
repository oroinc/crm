@not-automated
@drafts
Feature: In order send the customer detailed proposal while negotiating a deal
  As a Sales Representative
  I want to be able to create Quotes from Opportunity View

  Scenario: Feature background
    Given there is following "Commerce Customer" presented:
      | Account Name   |
      | Commersant2000 |
      | CommersSaint   |
    And there is following "Business Customer" presented:
      | Customer Name |
      | BusinessAtNet |

  Scenario: Activate/Deactivate "Quote Management Flow"
    Given I login as "Administrator" user
    And I go to System/ Workflows
    Then I should see "Quote Management Flow" in grid with following data:
    | Name                  | Active |
    | Quote Management Flow | No     |
    But I go to Quote Management Flow
    When I click "Activate"
    Then I should see "Quote Management Flow" in grid with following data:
    | Name                  | Active |
    | Quote Management Flow | Yes    |
    But I go to Quote Management Flow
    When I click "Deactivate"
    Then I should see "Quote Management Flow" in grid with following data:
    | Name                  | Active |
    | Quote Management Flow | No     |

  Scenario: Allowing user to create a quote
    Given "Quote Management Flow" is enabled
    When I go to  Sales/Opportunities
    And I click "Create Opportunity"
    And I fill in the following:
    | Opportunity name         | Account         | Status |
    | <Opportunity Name>  | <Account Name>  | <Status name> |
    And I click "Save And Close"
    Then I should see "Create Quote" status on the Opportunity view page
  Examples:
    | Opportunity Name  | Account Name   | Status name               |
    | Opportunity 1     | Commersant2000 | Open                      |
    | Opportunity 2     | Commersant2000 | Identification&Alignment  |
    | Opportunity 3     | Commersant2000 | Needs Analysis            |
    | Opportunity 4     | CommersSaint   | Solution Development      |
    | Opportunity 5     | CommersSaint   | Negotiation               |

  Scenario: Disallowing the user to create a quote
    Given "Quote Management Flow" is enabled
    And <Account Name> is not Commerce Customer
    And I go to Sales/Opportunities
    And I click "Create Opportunity"
    And I fill in the following:
    | Opportunity name    | Account         | Status        |
    | <Opportunity Name>  | <Account Name>  | <Status name> |
    And I click "Save And Close"
    Then I should not see "Send Quote" button on the Opportunity view page
  Examples:
  | Opportunity Name | Account Name  | Status name |
  | Opportunity 6    | BusinessAtNet | Closed Won  |
  | Opportunity 7    | BusinessAtNet | Closed Lost |

  Scenario: Create and send Quote
    Given I select "Opportunity 1"
    And I click "Create Quote"
    Then I should be on "Create Quote" page
    And "Customer" field should be pre-filled with "Commersant2000" Account name
    But I fill in the following:
    | PRODUCT                            | UNIT PRICE |
    | 1AB92 - Credit Card Pin Pad Reader | 10         |
    When I click "Save and Close"
    Then I should get back to Opportunity page
    And workflow step should be changed to "Quote Created"
    And I should see quote details on Quotes section in the Opportunity view page
    And "Edit", "Delete", "Expire Quote" and "View" actions should be available for the given quote at the Quote grid
    But I click "Commersant2000"
    Then Quote should be presented in "Quotes" grid

  Scenario: Multiple quotes can be created for a single Opportunity
    Given I go to Sales/Opportunities
    When I click "Opportunity 2"
    And I send "First Quote" Quote from the Opportunity View Page
    Then"Commersant2000" Commerce Customer  should see this quote in his Account
    And "Create Quote" button should still be available on the Opportunity view page
    But I send "Second Quote" Quote from the Opportunity View Page
    Then "Commersant2000" Commerce Customer should see this quote in his Account
    But I go to Sales/Opportunities
    When I click "Opportunity 2"
    Then the quotes are sorted by updated at date, most recent higher

  Scenario: Quotes Section are absent on Opportunities not related to Commerce customers
    Given I go to Sales/Opportunities
    When I click "Opportunity 6"
    Then I should not see Quotes Section

  Scenario: Edit Quote from Quote Section on Opportunity view
    Given I go to Sales/Opportunities
    And I click "Opportunity 2"
    When I select "Edit" action for "Second Quote" Quote
    And I'm edit entity
    And I click "Save And Close"
    Then I should see edited Quote on the Quotes grid
    And "Commersant2000" Commerce Customer should see edited Quote

  Scenario: Expire Quote from Quote Section on Opportunity view
    Given I go to Sales/Opportunities
    And I click "Opportunity 2"
    When I select "Expire" action for "Second Quote" Quote
    And I confirm "Expire" action
    Then the Quote should be expired
    And "Commersant2000" Commerce Customer should see "Second Quote" Quote

  Scenario: Delete Quote from Quote Section on Opportunity view
    Given I go to Sales/Opportunities
    And I click "Opportunity 2"
    When I select "Delete" action for "Second Quote" Quote
    And I confirm deletion
    Then I should not see Quote in the Quotes grid
    And "Commersant2000" Commerce Customer should see "Second Quote" Quote