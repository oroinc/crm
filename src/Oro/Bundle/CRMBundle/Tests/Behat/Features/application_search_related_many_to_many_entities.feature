@ticket-BAP-15693
@fixture-OroCRMBundle:searchManyToManyEntities.yml
Feature: Application search related many to many entities
  In order to find related entities: Account and Customer
  As a user
  I need to find them both after changing customer
  According to predefined search configuration

  Scenario: Search by mail parts
    Given I login as administrator
    And I follow "Search"
    And type "some_email@armyspy.com" in "search"
    When I press "Go"
    And I should see following search results:
      | Title                | Type          |
      | Mrs. July Robertson  | Contact       |
      | Mister customer      | Account       |

  Scenario: Change contact email
    Given I follow "Mrs. July Robertson"
    And I press "Edit Contact"
   And I fill form with:
     | Emails          | [changed_email@test.org] |
   And I save and close form

  Scenario: Search by mail parts again
    Given I follow "Search"
    And type "changed_email@test.org" in "search"
    And I press "Go"
    And I should see following search results:
      | Title                | Type          |
      | Mrs. July Robertson  | Contact       |
      | Mister customer      | Account       |
