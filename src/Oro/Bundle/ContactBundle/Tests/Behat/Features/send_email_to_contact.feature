@ticket-BAP-15902
@ticket-BAP-21309
@fixture-OroContactBundle:contacts.yml
@fixture-OroEmailBundle:templates.yml

Feature: Send email to contact
  In order to have ability work with contacts
  As administrator
  I need to have ability send email to contact

  Scenario: Feature Background
    Given I login as administrator

  Scenario: Send email with template
    Given I go to Customers/Contacts
    And I click view "Aadi AABERG" in grid
    And I click "More actions"
    When I click "Send email"
    And I fill "Send Email Form" with:
      | Apply template | test_template |
    And I click "Yes, Proceed"
    Then "Email Form" must contains values:
      | Subject | Test Subject          |
      | Body    | <h1>Test Content</h1> |
    When I click "Send"
    Then the email containing the following was sent:
      | Subject | Test Subject |
      | Body    | Test Content |

  Scenario: Add duplicated emails
    Given I go to Customers/Contacts
    And I click edit "Aadi AABERG" in grid
    When fill form with:
      | Emails | [test@example.org, test@example.org] |
    And save and close form
    Then I should see "Contact saved" flash message

  Scenario: Send email when contact has duplicated email
    Given I click "More actions"
    When I click "Send email"
    And I fill "Send Email Form" with:
      | Subject | Email from contact with duplicated email |
      | Body    | Sample content                           |
    And I click "Send"
    Then the email containing the following was sent:
      | To      | test@example.org                         |
      | Subject | Email from contact with duplicated email |
      | Body    | Sample content                           |

