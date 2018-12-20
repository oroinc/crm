@ticket-BAP-17495
@fixture-OroCRMBundle:contacts.yml
Feature: Email signature for send email to contact
  In order to send email with signature to contact on admin panel
  As a Admin
  I want to be able to edit signature by following suggested link

  Scenario: Send Email from contact view page
    Given I login as administrator
    And I go to Customers/Contacts
    And I click "View" on first row in grid
    And follow "More actions"
    And follow "Send email"
    When I follow "Add Signature"
    And I close ui dialog
    When I follow "My Configuration" link within flash message "You don't have a signature yet. Please add it in My Configuration."
    Then I should see "Email Configuration"
    And I should see "Signature Content"
