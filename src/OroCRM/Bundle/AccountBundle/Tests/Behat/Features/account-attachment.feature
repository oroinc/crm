Feature: Account attachment
  In order to have ability add attachments to account
  As a sales rep
  I need to have dedicated functionality on account page

  Scenario: Add attachment from view account page
    Given I login as administrator
    And the following account:
      | name          | extendDescription |
      | Charlie Sheen | <sentences(3)     |
    And I go to Customers/Accounts
    And click on Charlie Sheen in grid
    And follow "More actions"
    And press "Add attachment"
    When I fill "Attachment" form with:
      | File    | cat1.jpg    |
      | Comment | Sweet kitty |
    And press "Save"
    Then I should see "Attachment created successfully" flash message

  Scenario: View attachment
    Given I should see Sweet kitty in grid with following data:
      | File name | cat1.jpg |
      | File size | 76.77 KB |
    When I follow "cat1.jpg"
    Then I should see large image
