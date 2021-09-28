@regression
@ticket-CRM-9312
@fixture-OroContactUsBundle:email_notification_with_contact_reason_and_preferred_contact_method_fields.yml

Feature: Email notification with contact_reason and preferred contact method fields

  Scenario: Create notification rule
    Given I login as administrator
    And go to System/ Emails/ Notification Rules
    And click "Create Notification Rule"
    And fill form with:
      | Entity Name | Contact Request                                         |
      | Event Name  | Entity create                                           |
      | Template    | contact_request_create_notification_with_special_fields |
      | Users       | John Doe                                                |
    When I save and close form
    Then I should see "Notification Rule saved" flash message

  Scenario: Create contact request
    Given I go to Activities/ Contact Requests
    And click "Create Contact Request"
    And fill form with:
      | First Name               | Acme                                |
      | Last Name                | Acme                                |
      | Preferred contact method | Email                               |
      | Contact Reason           | Want to know more about the product |
      | Email                    | test@test.com                       |
      | Comment                  | Test comment                        |
    When I save and close form
    Then I should see "Contact request has been saved successfully" flash message
    And Email should contains the following:
      | To      | admin@example.com                                                                     |
      | Subject | New contact request from Acme Acme                                                    |
      | Body    | Contact reason: Want to know more about the product - Preferred contact method: Email |
