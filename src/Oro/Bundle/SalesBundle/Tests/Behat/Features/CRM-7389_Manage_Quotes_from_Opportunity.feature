@not-automated
@drafts

Feature: In order send the customer detailed proposal while negotiating a deal
  As a Sales Representative
  I want to be able to create Quotes from Opportunity View

  Scenario: Activate/Deactivate "Quote Management Flow"
    Given "Quote Management Flow" is enabled
    And |I login as Sales Manager user
    And |I go to next pages: System/ Workflows
    And |I should see "Quote Management Flow" in grid with following data:
    | Name                  | Active  |
    | Quote Management Flow | Yes     |
    When |I click "Deactivate" for "Quote Management Flow"
    Then |I should see "Quote Management Flow" in grid with following data:
    | Name                  | Active  |
    | Quote Management Flow | No      |
    When |I click "Activate" for "Quote Management Flow"
    Then |I should see "Quote Management Flow" in grid with following data:
    | Name                  | Active  |
    | Quote Management Flow | Yes     |

  Scenario: Allowing the user to create a quote
    Given "Quote Management Flow" is enabled
    And <Account Name> is Commerce Customer
    And |I go to next pages: Sales/ Opportunities/
    And |I click on "Create Opportunity"
    And |I fill in the following:
    | Owner     | Opportunity name    | Account         | Status        |
    | John Doe  | <Opportunity Name>  | <Account Name>  | <Status name> |
    And |I click on "Save And Close" button
    Then |I should see "Send Quote" button on the Opportunity view page
    And |I click "Send Quote" button
    And I fill out the necessary fields with the valid data
    And I send the Quote
    Then Commerce Customer can see the Quote in his Account
  Examples:
  | Opportunity Name  | Account Name  | Status name               |
  | Opportunity 1     | Mister A      | Open                      |
  | Opportunity 2     | Mister B      | Identification&Alignment  |
  | Opportunity 3     | Mister C      | Needs Analysis            |
  | Opportunity 4     | Mister D      | Solution Development      |
  | Opportunity 5     | Mister E      | Negotiation               |

  Scenario: Disallowing the user to create a quote
    Given "Quote Management Flow" is enabled
    And <Account Name> is not Commerce Customer
    And |I go to next pages: Sales/ Opportunities
    And |I click on "Create Opportunity"
    And |I fill in the following:
    | Owner     | Opportunity name    | Account         | Status        |
    | John Doe  | <Opportunity Name>  | <Account Name>  | <Status name> |
    And |I click on "Save And Close" button
    Then |I should not see "Send Quote" button on the Opportunity view page
  Examples:
  | Opportunity Name  | Account Name  | Status name |
  | Opportunity 6     | Mister F      | Closed Won  |
  | Opportunity 7     | Mister G      | Closed Lost |

  Scenario: Create and send Quote
    Given |I select "Opportunity 1" from Opportunities grid
    And |I click on "Create Quote"
    Then separate "Create Quote" page should be shown
    And I should see that "Customer" field is prefilled with "Mister A" Account name
    And I fill out all necessary fields
    And |I press "Save and Close"
    Then I should get back to Opportunity page
    And workflow step should be changed to "Quote sent"
    And I should see quote details on Quotes section in the Opportunity view page
    And Edit, Delete, View, Expire actions should be available for the given quote at the Quote grid
    And Commerce Customer can see the Quote in his Account


  Scenario: Multiple quotes can be created for a single Opportunity
    Given |I go to next pages: Sales/ Opportunities
    And I select open Opportunity related to Commerce Customer "Mister Jungle"
    When I send Quote "First Quote" from the Opportunity View Page
    Then Commerce Customer "Mister Jungle" should see this quote in his Account
    And "Create Quote" button should still be available on the Opportunity view page
    When I send Quote "Second Quote" from the Opportunity View Page
    Then Commerce Customer "Mister Jungle" should see this quote in his Account
    When I send Quote "Third Quote" from the Opportunity View Page
    Then Commerce Customer "Mister Jungle" should see this quote in his Account

  Scenario: Quotes Section should not be displayed at Opportunities that are not related to Commerce customers
    Given I select the Opportunity that is not related to Commerce customer
    And I am in the Opportunity view page
    Then |I should not see Quotes Section

  Scenario: Quotes in grid are sorted by Updated at date, most recent higher
    Given there are few quotes related to the opportunity
    When I go to the opportunity view page
    Then I should see the quotes on the Quote grid
    And the quotes are sorted by updated at date, most recent higher

  Scenario: Edit Quote from Quote Section on Opportunity view
    Given there is an open opportunity related to Commerce Customer
    And there is a Quote related to that Opportunity
    And |I go to next pages: Sales/ Opportunities
    And |I select this opportunity on the opportunity grid
    And I select "Edit" action for the Quote
    And I edit Quote
    And |I click on "Save And Close" button
    Then I should see edited Quote on the Quotes grid
    And when the Commerce Customer logs into his account
    Then he should see edited Quote on the Quotes grid

  Scenario: Expire Quote from Quote Section on Opportunity view
    Given there is an open opportunity related to Commerce Customer
    And there is a Quote related to that Opportunity
    And |I go to next pages: Sales/ Opportunities
    And |I select this opportunity on the opportunity grid
    And I select "Expire" action for the Quote
    And I confirm "Expire" action
    Then the Quote should expire

  Scenario: Delete Quote from Quote Section on Opportunity view
    Given there is an open opportunity reed to Commerce Customer
    And there is a Quote related to that Opplatortunity
    And |I go to next pages: Sales/ Opportunities
    And |I select this opportunity on the opportunity grid
    And I select "Delete" action for the Quote
    And |I confirm deletion
    Then |I should not see Quote in the Quotes grid