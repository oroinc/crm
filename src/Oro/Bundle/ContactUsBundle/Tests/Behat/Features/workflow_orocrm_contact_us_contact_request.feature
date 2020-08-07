@fixture-OroContactUsBundle:3-contact-requests.yml
Feature: Workflow orocrm_contact_us_contact_request
    In order to resolve contact requests or convert them to leads or opportunities
    As a sales person
    I should be able to perform "Convert to Opportunity", "Convert to Lead" and "Resolve" workflow transitions from contact request page

    Scenario: Preconditions
        Given I login as administrator

    Scenario: Convert to Opportunity
        When I go to Activities/ Contact Requests
        And I click view "Arthur" in grid
        Then I should see "Open"
        And I click "Convert to Opportunity"
        And I wait 2 seconds
        And I should see that "UiDialog Title" contains "Convert To Opportunity"
        And I fill "Contact Request Workflow Convert to Opportunity Dialog Form" with:
            | Account       | Company A        |
            | Contact       | John Doe         |
            | Budget Amount | 1234             |
            | Feedback      | A note to myself |
        And I click "Submit"
        Then I should see "Converted to opportunity"
        Then I should see "Opportunity Arthur Doe"
        When I click "Arthur Doe"
        Then I should see an opportunity with:
            | Opportunity Name    | Arthur Doe           |
            | Budget Amount       | $1,234.00            |
            | Account             | Company A            |
            | Contact             | John Doe             |
            | Additional Comments | A note to myself     |

    Scenario: Convert to Lead
        When I go to Activities/ Contact Requests
        And I click view "Ben" in grid
        Then I should see "Open"
        And I click "Convert to Lead"
        And I wait 2 seconds
        And I should see that "UiDialog Title" contains "Convert To Lead"
        And I fill "Contact Request Workflow Convert to Lead Dialog Form" with:
            | Feedback      | Another note |
        And I click "Submit"
        Then I should see "Converted to lead"
        Then I should see "Lead Ben Doe"
        When I click "Ben Doe"
        Then I should see an opportunity with:
            | First Name          | Ben               |
            | Last Name           | Doe               |
            | Emails              | [ben@example.org] |
            | Phones              | [(234) 567-8901] |
            | Additional Comments | Another note      |

    Scenario: Resolve
        When I go to Activities/ Contact Requests
        And I click view "Charlie" in grid
        Then I should see "Open"
        And I click "Resolve"
        And I wait 2 seconds
        And I should see that "UiDialog Title" contains "Resolve"
        And I fill "Contact Request Workflow Resolve Dialog Form" with:
            | Feedback | Something noteworthy |
        And I click "Submit"
        Then I should see "Resolved"
