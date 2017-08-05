@regression
@skip
# @todo: Unskip when CRM-7821 will be resolved
@fixture-OroCRMBundle:comment-activity.yml
Feature: Comment activity feature
  In order to have ability manage contact activity
  As OroCRM sales rep
  I need to view, filter, paginate activities in activity list

  Scenario: Contact grid
    Given I login as administrator
    And go to Customers/Contacts
    And click view Charlie in grid

  Scenario Outline: Comment different type of activities
    Given collapse "<Activity title>" in activity list
    When I add activity comment with:
      | Message    | <Comment>           |
    Then I should see <Comment> text in activity
    And I collapse "<Activity title>" in activity list

    Examples:
      | Activity title                       | Comment                |
      | Merry Christmas                      | It's so nice           |
      | Charlie works hard                   | Really hard            |
      | Contract sign in                     | Don't forget your pen  |
      | Proposed Charlie to star in new film | Offer him a nice bonus |

  Scenario: Attach image to comment
    Given collapse "Contact with Charlie" in activity list
    When I add activity comment with:
      | Message    | Ask how his mood |
      | Attachment | cat0.jpg          |
    Then I should see Ask how his mood text in activity
    And  I should see Comments (1) text in activity
    When I click "cat0.jpg"
    Then should see large image
    And I close large image preview
    And I collapse "Ask how his mood" in activity list

  Scenario: Edit comment
    Given I login as "misty" user
    And go to Customers/Contacts
    And click view Charlie in grid
    And collapse "Contact with Charlie" in activity list
    Then I should see John Doe added text in activity
    When I edit "Ask how his mood" activity comment with:
      | Message    | Just wish a nice day |
    Then I should see Just wish a nice day text in activity
    Then I should see updated by Misty Grant text in activity
    When I add activity comment with:
      | Message    | Give me a report about your collaboration |
    Then  I should see Comments (2) text in activity

  Scenario: Delete comment
    Given I delete "Just wish a nice day" activity comment
    Then I should see Comments (1) text in activity
