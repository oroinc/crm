@regression
@ticket-CRM-8723
@ticket-BAP-15117
@fixture-OroSalesBundle:opportunity_send_email_action.yml
@fixture-OroSalesBundle:opportunity_email_template_with_account_name_content.yml

Feature: Opportunity send email action
  Customer email address title should be taken from contact
  Customer email template with a account name variable is displayed correctly after sending
  In case contact do not has `NamePrefix` && `FirstName` && `MiddleName` && `LastName` && `NameSuffix` title should be
  created from opportunity record

  Scenario: Checks opportunities grid
    Given I login as administrator
    And I go to Sales/ Opportunities
    And I should see following grid:
      | Opportunity name                     |
      | opportunity w contact no email       |
      | opportunity with account name        |
      | opportunity w contact no name        |
      | opportunity w contact name and email |
      | opportunity wo contact               |

  Scenario: Check account name variables is displayed correctly after sending
    Given I go to Sales/ Opportunities
    And click "View" on first row in grid
    And click "More actions"
    And click "Send email"
    When I fill "Send Email Form" with:
      | To             | admin@example.com          |
      | Apply template | opportunity_email_template |
    And click "Yes, Proceed"
    And click "Send"
    Then I should see "The email was sent" flash message
    When I click My Emails in user menu
    Then I should see following grid:
      | Contact | Subject                           |
      | me      | Subject AccountName: test Account |

  Scenario: Check opportunity contact that has only name, "Send Email" dialog should have empty field "To"
    Given I go to Sales/ Opportunities
    And I click view opportunity w contact no email in grid
    And I follow "More actions"
    And click "Send email"
    Then "Email Form" must contains values:
      | From | "John Doe" <admin@example.com> |
    When I click "Send"
    Then I should see "Email Form" validation errors:
      | ToField | This value contains not valid email address. |
      | Subject | This value should not be blank.              |
    And I close ui dialog

  Scenario: Check opportunity contact that has only email address, "Send Email" dialog should have field "To" containing opportunity title and contact email
    Given I go to Sales/ Opportunities
    And I click view opportunity w contact no name in grid
    And I follow "More actions"
    And I click "Send email"
    Then "Email Form" must contains values:
      | From    | "John Doe" <admin@example.com>                                          |
      | ToField | ["opportunity w contact no name" <contactEmail2@example.com> (Contact)] |
    And I close ui dialog

  Scenario: Check opportunity contact that has name and email, "Send Email" dialog should have field "To" containing contacts title and email
    Given I go to Sales/ Opportunities
    And I click view opportunity w contact name and email in grid
    And I follow "More actions"
    And I click "Send email"
    Then "Email Form" must contains values:
      | From    | "John Doe" <admin@example.com>                                    |
      | ToField | ["test Contact with Email" <contactEmail1@example.com> (Contact)] |
    And I close ui dialog

  Scenario: Check opportunity that do NOT has contact, "Send Email" dialog should have empty field "To"
    Given I go to Sales/ Opportunities
    And I click view opportunity wo contact in grid
    And I follow "More actions"
    And click "Send email"
    Then "Email Form" must contains values:
      | From | "John Doe" <admin@example.com> |
    When I click "Send"
    Then I should see "Email Form" validation errors:
      | ToField | This value contains not valid email address. |
      | Subject | This value should not be blank.              |
    And I close ui dialog
