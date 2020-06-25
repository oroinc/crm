# Magento integration is disabled in CRM-9202
@skip
@ticket-BAP-13174
@automatically-ticket-tagged
Feature: Embedded form
  In order to embedded forms
  As an OroCRM admin
  I need to be able to create new embedded form and submit it

  Scenario: Create new embedded form
    Given I login as administrator
    And I go to System/Integrations/Embedded Forms
    And click "Create Embedded Form"
    When I fill "Embedded Form" with:
      | Title           | Magento contact us form              |
      | Form Type       | Magento Contact Us Request           |
      | CSS             | #testId { position: absolute; }      |
      | Success Message | Form has been submitted successfully |
    And I save and close form
    Then I should see "Form has been saved successfully" flash message

  Scenario: Submit Magento contact us form
    Given I fill "Magento contact us form" with:
      | First name               | John                 |
      | Last name                | Doe                  |
      | Preferred contact method | Email                |
      | Email                    | john-doe@example.com |
      | Comment                  | New comment          |
    When I press "Submit" in "Magento contact us form"
    Then I should see "Form has been submitted successfully"
    And I go to Activities/ Contact Requests
    And I should see John in grid with following data:
      | LAST NAME | Doe                  |
      | EMAIL     | john-doe@example.com |
