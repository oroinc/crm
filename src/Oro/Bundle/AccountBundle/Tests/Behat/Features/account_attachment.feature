@regression
@fixture-OroAccountBundle:account-with-contact.yml
Feature: Account attachment
  In order to have ability add attachments to account
  As a sales rep
  I need to have dedicated functionality on account page

  Scenario: Add attachment from view account page
    Given I login as administrator
    And I go to Customers/Accounts
    And click view "Charlie Sheen" in grid
    And follow "More actions"
    And click "Add attachment"
    When I fill "Attachment Form" with:
      | File    | cat1.jpg    |
      | Comment | Sweet kitty |
    And click "Save"
    Then I should see "Attachment created successfully" flash message

  Scenario: View attachment
    Given I should see Sweet kitty in grid with following data:
      | File name | cat1.jpg |
      | File size | 76.77 KB |
    When I follow "cat1.jpg"
    Then I should see large image
    And I close large image preview

  Scenario: Edit attachment
    Given I click Edit cat1.jpg in grid
    When I fill "Attachment Form" with:
      | File    | cat2.jpg |
      | Comment | So cute  |
    And click "Save"
    Then I should see "Attachment updated successfully" flash message
    Then I should see cat in grid with following data:
      | File name | cat2.jpg |
      | File size | 61.51 KB |
    And I follow "cat2.jpg"
    And I should see large image
    And I close large image preview

  Scenario: Email attachment
    Given follow "More actions"
    And click "Send email"
    And fill form with:
      | Subject | Hello World |
      | To      | [Another Contact]  |
    When select cat2 as email attachment from record
    And click "Send"
    Then I should see "The email was sent" flash message
    And I collapse "Hello World" in activity list
    And I should see cat2.jpg text in activity

  Scenario: Delete attachment
    Given I click Delete cat2.jpg in grid
    When I click "Yes, Delete"
    Then I should see "Item deleted" flash message

  Scenario: Email attachment filtering based on system configuration
    When follow "More actions"
    And click "Add attachment"
    And I fill "Attachment Form" with:
      | File    | cat1.jpg    |
      | Comment | Sweet kitty |
    And click "Save"
    Then I should see "Attachment created successfully" flash message

    When follow "More actions"
    And click "Add attachment"
    And I fill "Attachment Form" with:
      | File    | cat2.jpg |
      | Comment | So cute  |
    And click "Save"
    Then I should see "Attachment created successfully" flash message

    When follow "More actions"
    And click "Add attachment"
    And I fill "Attachment Form" with:
      | File    | example.pdf |
      | Comment | Important   |
    And click "Save"
    Then I should see "Attachment created successfully" flash message
    And I should see following "Attachment Grid" grid:
      | File name   |
      | cat1.jpg    |
      | cat2.jpg    |
      | example.pdf |

    When I go to System / Configuration
    And I follow "System Configuration/General Setup/Email Configuration" on configuration sidebar
    And uncheck "Use default" for "Maximum Attachment Size, Mb" field
    And I fill in "Maximum Attachment Size, Mb" with "0.065"
    And I save form
    Then I should see "Configuration saved" flash message

    When I follow "System Configuration/General Setup/Upload Settings" on configuration sidebar
    And uncheck "Use default" for "File MIME types" field
    And I unselect "application/pdf" option from "File MIME Types"
    And I save form
    Then I should see "Configuration saved" flash message

    When I go to Customers/Accounts
    And click view "Charlie Sheen" in grid
    And follow "More actions"
    And click "Send email"
    And I click "From Record"
    Then I should see "cat2.jpg (61.51KB"
    And I should not see "cat1.jpg ("
    And I should not see "example.pdf ("
