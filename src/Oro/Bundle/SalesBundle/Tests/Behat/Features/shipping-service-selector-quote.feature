@ticket-BB-9268
# we need 2 flat rate shipping methods, so fixtures are loaded twice
@fixture-OroFlatRateShippingBundle:FlatRateIntegration.yml
@fixture-OroCheckoutBundle:Shipping.yml
@fixture-OroFlatRateShippingBundle:FlatRateIntegration.yml
@fixture-OroCheckoutBundle:Shipping.yml
@fixture-OroPaymentTermBundle:PaymentTermIntegration.yml
@fixture-OroCheckoutBundle:Payment.yml
@fixture-shipping_selector_quote.yml
@fixture-OroCheckoutBundle:InventoryLevel.yml
Feature: Default Shipping Service Selector on Quote Edit page

  Scenario: Create a quote and finished checkout with Flat Rate Shipping Method
    Given There are products in the system available for order
    And I signed in as AmandaRCole@example.org on the store frontend
    And I click "Quick Order Form"
    And I add product SKU123 with quantity 1 to quick order form
    And I click "Add to Shopping List"
    And I open page with shopping list Shopping list
    And I request a quote from shopping list "Shopping list" with data:
      | PO Number | PONUMBER1 |

    And I login as administrator
    And I go to Sales/ Requests For Quote
    And I open RFQ view page on backend with id "1"
    And I click "Create Quote"
    And I fill in "Unit Price" with "22"
    And I select "801 Scenic Hwy, HAINES CITY FL US 33844" from "Shipping Address" field
    And I click on "Calculate Shipping"
    And I fill "Shipping Method Form" with:
      | Method | Flat Rate |
    And I save and close form
    And I click "Send to Customer"
    And I click "Send" in modal window

    And I signed in as AmandaRCole@example.org on the store frontend
    And I click "Account"
    And I click "Quotes"
    And I click on View in grid
    And click "Accept and Submit to Order"
    And click "Submit"
    And I select "801 Scenic Hwy, HAINES CITY FL US 33844" on the "Billing Information" checkout step and press Continue
    And on the "Shipping Information" checkout step I press Continue
    And on the "Shipping Method" checkout step I press Continue
    And on the "Payment" checkout step I press Continue
    Then I should see "Subtotal $22.00"
    Then I should see "Shipping $3.00"
    Then I should see "Total $25.00"
    When I click "Submit Order"
    Then I see the "Thank You" page with "Thank You For Your Purchase" title
