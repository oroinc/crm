@ticket-BAP-14224
@automatically-ticket-tagged
@fixture-OroFlatRateShippingBundle:FlatRateIntegration.yml
@fixture-OroPaymentTermBundle:PaymentTermIntegration.yml
@fixture-OroCheckoutBundle:Checkout.yml
@fixture-opportunities.yml
Feature: In order send the customer detailed proposal while negotiating a deal
  As a Sales Representative
  I want to be able to create Quotes from Opportunity View

  Scenario: "Quote flow" activation
    Given I login as administrator
    When I go to System/Workflows
    And I check "Opportunity" in Related Entity filter
    Then I should see "Quote flow" in grid with following data:
      | Active                  | No               |
      | Exclusive Active Groups | quote_management |
    When I click Activate Quote flow in grid
    And I press "Activate"
    Then I should see "Workflow activated" flash message
    And I should see "Quote flow" in grid with following data:
      | Active                  | Yes              |
      | Exclusive Active Groups | quote_management |

  Scenario: Disallowing the user to create a quote
    Given I go to Sales/Opportunities
    When I reset Status filter
    And I click View Testing opportunity_2 in grid
    Then I should see Testing opportunity2 with:
      | Customer | CommSkyNet |
      | Status   | Closed Won |
    And I should not see "Create Quote"
    When I go to Sales/Opportunities
    And I reset Status filter
    And click View Testing opportunity21 in grid
    Then I should see Testing opportunity21 with:
      | Customer | CommSkyNet  |
      | Status   | Closed Lost |
    And I should not see "Create Quote"

  Scenario: Allowing user to create a quote
    Given I go to Sales/Opportunities
    When click View Testing opportunity1 in grid
    Then I should see Testing opportunity1 with:
      | Customer | CommSkyNet |
      | Status   | Open       |
    And I should see "Create Quote"

  Scenario: Create Quote
    Given I press "Create Quote"
    When fill "Quote Line Items" with:
      | Product    | SKU123 |
      | Unit Price | 10     |
    And I fill form with:
      | Customer      | Company A   |
      | Customer User | Amanda Cole |
    And I save and close form
    And agree that shipping cost may have changed
    Then I should see "Quote has been saved" flash message
    And I should see "Quote Created"
    And I should see John Doe in grid with following data:
      | Expired | No    |
      | Step    | Draft |
    When I click View John Doe in grid
    Then I should see following buttons:
      | Edit             |
      | Delete           |
      | Clone            |
      | Send to Customer |
    When I click "Company A"
    Then I should see John Doe in grid with following data:
      | Expired | No    |
      | Step    | Draft |

  Scenario: Check quotes sorting
    Given I go to Sales/Opportunities
    When I click View Opportunity1 in grid
    And I sort Quotes Grid by Updated At
    Then Quotes Grid must be sorted ascending by updated date

  Scenario: Multiple quotes can be created for a single Opportunity
    Given I go to Sales/Opportunities
    When I click View Testing opportunity1 in grid
    And I click View John Doe in grid
    And click "Send to Customer"
    And I fill in "To" with "AmandaRCole@example.org"
    And press "Send"
    Then I should see Quote with:
      | Opportunity     | Testing opportunity1 |
      | Internal Status | Sent to Customer     |
      | Customer        | Company A            |
      | Customer User   | Amanda Cole          |
    And I should see following buttons:
      | Cancel               |
      | Expire               |
      | Create new Quote     |
      | Declined by Customer |

  Scenario: Quotes Section are absent on Opportunities not related to Commerce customers
    Given I go to Sales/Opportunities
    When I click View Opportunity 5 in grid
    Then I should not see "Quotes"

  Scenario: Check quote appearing in buyer account
    Given I login as AmandaRCole@example.org buyer
    When open Customer Quotes List page
    Then records in current Customer Quotes Grid should be 1
    When I click "View"
    Then I should see SKU123 in Quote View Grid with following data:
      | Quantity   | 1 item or more |
      | Unit Price | $10.00         |

  Scenario: Edit Quote from Quote Section on Opportunity view
    Given I login as administrator
    And go to Sales/Opportunities
    And I click View Testing opportunity1 in grid
    When I click Edit Sent to Customer in grid
    And fill form with:
      | Customer    | Company A                         |
      | Valid Until | <DateTime:Dec 20, 2018, 11:00 AM> |
      | PO Number   | 123123                            |
    And save and close form
    Then I should see Quote with:
      | Customer    | Company A     |
#    @todo Uncomment when bug will resolved. BAP-12124.
#      | Valid Until | Dec 20, 2018, 11:00 AM |
      | Valid Until | Dec 20, 2018  |
      | Valid Until | 11:00 AM      |
      | PO Number   | 123123        |

  Scenario: Expire Quote from Quote Section on Opportunity view
    When I click "Expire"
    And click "Mark as Expired"
    Then I should see Quote with:
      | Internal Status | Expired |
    And I should see following buttons:
      | Reopen |
    And I should not see following buttons:
      | Cancel               |
      | Expire               |
      | Create new Quote     |
      | Declined by Customer |

  Scenario: Check edited quote from buyer account
    Given I login as AmandaRCole@example.org buyer
    When I open Customer Quotes List page
    Then records in current Customer Quotes Grid should be 1
    And I should see 1 in Customer Quotes Grid with following data:
#    @todo Uncomment when bug will resolved. BAP-12124.
#      | Valid Until | Dec 20, 2018, 11:00 AM |
      | PO Number   | 123123                 |

  Scenario: Check expired quote from buyer account
    When I click "View"
    Then I should see "This quote has expired. You may submit a new request for quote."

  Scenario: Delete Quote from Quote Section on Opportunity view
    Given I login as administrator
    When I go to Sales/Opportunities
    And I click View Testing opportunity1 in grid
    And click Delete John Doe in grid
    And confirm deletion
    Then I should see "Quote deleted" flash message

  Scenario: Check deleted quote from buyer account
    Given I login as AmandaRCole@example.org buyer
    When open Customer Quotes List page
    Then there is no records in Customer Quotes Grid
