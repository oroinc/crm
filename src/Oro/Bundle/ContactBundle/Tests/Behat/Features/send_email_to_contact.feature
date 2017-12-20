@fixture-OroContactBundle:contacts.yml
@fixture-OroEmailBundle:templates.yml

Feature: Send email to contact
  In order to have ability work with contacts
  As administrator
  I need to have ability send email to contact

  Scenario: Choose email template
    Given I login as administrator
    And I go to Customers/Contacts
    And I click view "Aadi AABERG" in grid
    And I click "More actions"
    And I click "Send email"
    And I fill "Send Email Form" with:
      | Apply template | test_template |
    And I click "Yes, Proceed"
    And "Email Form" must contains values:
      | Subject | Test Subject          |
      | Body    | <h1>Test Content</h1> |
