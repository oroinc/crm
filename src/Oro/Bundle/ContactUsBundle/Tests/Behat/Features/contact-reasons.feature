Feature: Contact Reasons
  In order to manage contact requests
  As an Administrator
  I want to be able to manage the contact reasons dictionary

  Scenario: Create new contact reason
    Given I login as administrator
    And go to Activities/ Contact Reasons
    And press "Create Contact Reason"
    And fill "Contact Reason Form" with:
      |Label         |New reason                         |
    When I save and close form
    Then I should see "Contact reason has been saved successfully" flash message
    Then I should see following records in grid:
      | New reason |
