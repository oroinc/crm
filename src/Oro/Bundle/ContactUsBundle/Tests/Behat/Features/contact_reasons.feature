Feature: Contact Reasons
  In order to manage contact requests
  As an Administrator
  I want to be able to manage the contact reasons dictionary

  Scenario: Create new contact reason and create contact request with it
    Given I login as administrator
    And go to System/ Contact Reasons
    And click "Create Contact Reason"
    And fill "Contact Reason Form" with:
      | Label | New reason |
    When I save and close form
    Then I should see "Contact reason has been saved successfully" flash message
    And I should see following records in grid:
      | New reason |
    And I should see following actions for New reason in grid:
      | Edit   |
      | Delete |
    When I go to Activities/ Contact Requests
    And click "Create Contact Request"
    And fill form with:
      |First Name              |Test              |
      |Last Name               |Tester            |
      |Preferred contact method|Email             |
      |Email                   |qa@oroinc.com     |
      |Contact Reason          |New reason        |
      |Comment                 |Test Comment      |
    When I save and close form
    Then I should see "Contact request has been saved successfully" flash message

  Scenario: Edit contact reason
    Given I login as administrator
    And go to System/ Contact Reasons
    And I should see New reason in grid with following data:
      | Label | New reason |
    And I click edit "New reason" in grid
    Then I should see "New reason"
    When fill "Contact Reason Form" with:
      | Label | Changed reason |
    And I save and close form
    And I should see "Contact reason has been saved successfully" flash message
    And I should see following records in grid:
      | Changed reason |
    When I go to Activities/ Contact Requests
    And I click view "qa@oroinc.com" in grid
    And I should see "Changed reason"

  Scenario: Delete contact reason with soft delete
    Given I login as administrator
    And go to System/ Contact Reasons
    And I should see Changed reason in grid with following data:
      | Label | Changed reason |
    And I click delete "Changed reason" in grid
    When I confirm deletion
    And there is no "Changed reason" in grid
    And I go to Activities/ Contact Requests
    And I click view "qa@oroinc.com" in grid
    And I should see "Changed reason"
    And I go to Activities/ Contact Requests
    And I click edit "qa@oroinc.com" in grid
    And I should see "Choose contact reason..."
