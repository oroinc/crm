@non-automated
Feature: Create Opportunity from related entity views
  In order to ease opportunity management
  as a Sales Rep
  I should have a possibility to create Opportunity from related entity views

  Scenario: Feature background
    Given following "User" exists:
    | Username   | Password  | Role      |
    | johnconnor | Qwe123qwe | Sales Rep |
    And following "Account" exists:
    | Account Name |
    | SkyNet       |
    And following "Contact" exists:
    | First Name | Second Name | Phones        | Accounts |
    | Mr.        | Robot       | +150510001101 | SkyNet   |
    And following "Magento Customer" exists:
    | Email         | Account | Contact   |
    | sn@skynet.com | SkyNet  | Mr. Robot |
    And following "Business Customer" exists:
    | Customer Name | Account |
    | BusSkyNet     | Skynet  |
    And following "Commerce Customer" exists:
    | Name       |
    | CommSkyNet |

  Scenario: Sales Rep creates Opportunity for Account
    Given I login as "johnconnor" user
    And I go to Accounts
    And I open "SkyNet" entity
    When I go to More Actions/Create Opportunity
    And I fill in "Opportunity name" with "First Invasion"
    And I save and close form
    Then I should see "Opportunity" view page
    And "Account" should be filled with "SkyNet"

  Scenario: Sales Rep creates Opportunity for Account
    Given I go to Magento Customers
    And I open "Mr. Robot" entity
    When I go to More Actions/Create Opportunity
    And I fill in "Opportunity name" with "Second Invasion"
    And I save and close form
    Then I should see "Opportunity" view page
    And "Account" should be filled with "Mr. Robot"

  Scenario: Sales Rep creates Opportunity for Account
    Given I go to Business Customers
    And I open "BusSkyNet" entity
    When I go to More Actions/Create Opportunity
    And I fill in "Opportunity name" with "Third Invasion"
    And I save and close form
    Then I should see "Opportunity" view page
    And "Account" should be filled with "BusSkyNet"

  Scenario: Sales Rep creates Opportunity for Account
    Given I go to Customers
    And I open "CommSkyNet" entity
    When I go to More Actions/Create Opportunity
    And I fill in "Opportunity name" with "Fourth Invasion"
    And I save and close form
    Then I should see "Opportunity" view page
    And "Account" should be filled with "CommSkyNet"
