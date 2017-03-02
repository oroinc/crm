Feature: Embedded form
  In order to embedded forms
  As an OroCRM admin
  I need to be able to create new embedded form and submit it

  Scenario: Create new embedded form
    Given I login as administrator
    And I go to System/Integrations/Embedded Forms
    And press "Create Embedded Form"
    When I fill "Embedded Form" with:
      | Title     | Magento contact us form              |
      | Form Type | oro_magento_contact_us.embedded_form |
    And I save and close form
    Then I should see "Form has been saved successfully" flash message

  Scenario: Submit Magento contact us form
    Given I am on Submit Magento contact us form page
    When I fill "Magento contact us form" with:
      | First name               | John                                      |
      | Last name                | Doe                                       |
      | Preferred contact method | oro.contactus.contactrequest.method.email |
      | Email                    | john-doe@example.com                      |
      | Comment                  | New comment                               |
    And I press "Submit"
    Then I should see "Form has been submitted successfully"

  Scenario: Checking submitted data in linked channel
    Given I am on Contact Requests page
    And I should see John in grid with following data:
      | LAST NAME | Doe                  |
      | EMAIL     | john-doe@example.com |
