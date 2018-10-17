@ticket-BAP-15693
@fixture-OroCRMBundle:searchManyToManyEntities.yml
Feature: Application search related many to many entities
  In order to find related entities: Account and Customer
  As a user
  I need to find them both after changing customer
  According to predefined search configuration

  Scenario: Search by mail parts
    Given I login as administrator
    And I click "Search"
    And type "some_email@armyspy.com" in "search"
    When I click "Search Submit"
    And I should see following search results:
      | Title                | Type          |
      | Mrs. July Robertson  | Contact       |
      | Mister customer      | Account       |

  Scenario: Change contact email
    Given I follow "Mrs. July Robertson"
    And I click "Edit Contact"
   And I fill form with:
     | Emails          | [changed_email@test.org] |
   And I save and close form

  Scenario: Search by mail parts again
    Given I click "Search"
    And type "changed_email@test.org" in "search"
    And I click "Search Submit"
    And I should see following search results:
      | Title                | Type          |
      | Mrs. July Robertson  | Contact       |
      | Mister customer      | Account       |
