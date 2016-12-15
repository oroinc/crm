@not-automated
Feature: Creating Accounts for Commerce Customers
In order to manage commerce customers after CRM/Commerce integration more efficiently
As an Administrator
I want to be able to create CRM accounts for Commerce Customers

  Background:
    Given a successful installation CRM and Commerce
    And root customer named "Company A"
    And children customer named Company A - East Division
    And children customer named Company A - West Division
    And an owner named "John Doe"
    And Account named "Body Toning"
    And Account named "Daddy's Pie" with customer user "Marlene Bradley"

# Req ID CRM-6111-5, CRM-6111-6, Req ID CRM-6111-7
Scenario: Creating CRM Accounts for existing customers if I selected to create accounts
#only for root customers, for every customer, and making reverse action

  Given I log in as Administrator
  When I go to Customers/ Customers
    Then I should see the following on Customers grid:
      |	NAME                        | ACCOUNT   |
      |	Company A                   | Company A |
      |	Company A - East Division   | Company A |
      |	Company A - West Division   | Company A |
    And I go to System/ Configuration/
    And I go to Integrations
    And I go to "CRM And Commerce" section
    And I select Creation New Account "For each Commerce Customer"
    And I see notification message
    And I press "Save Settings" button
    And I see "Configuration saved" flash message
    And I go to System/ Jobs
    And I see status "Success" for "oro.customer_account.reassign_customer_account" job
    And I go to Customers/ Customers
    Then I should see the following on Customers grid:
      | NAME                        | ACCOUNT                   |
      | Company A                   | Company A                 |
      | Company A - East Division   | Company A - East Division |
      | Company A - West Division   | Company A - West Division |
    When I go to System/ Configuration/ CRM/ Commerce Integration/ Account
    And I select CRM Account Creation Strategy "CRM Accounts only for root customers"
    And I press "Save Settings" button
    And I see "Configuration saved" flash message
    And I go to System/ Jobs
    And I see status "Success" for "oro.customer_account.reassign_customer_account" job
    And I go to Customers/ Customers
    Then I should see the following on Customers grid:
      | NAME                      | ACCOUNT   |
      | Company A                 | Company A |
      | Company A - East Division | Company A |
      | Company A - West Division | Company A |

# Req ID CRM-6111-3, CRM-6111-5
Scenario: Creating CRM Account from back-end when creating new Commerce Customer

    Given I go to Customers/ Customers
    And press "Create Customer" button
    And I fill out the "Owner" field with "John Doe"
    And I fill out the "Name" field with "Magento Company",
    And I see "Account" dropdown on the Create Customer form
    And I press "Save And Close" button
    Then I see that "Account" field is marked red
    And there is a message "This value should not be blank."
    And when I press "+" (Create Account) button for the Account field
    And Create Account popup is displayed
    And I fill out the following data:
      | Owner     | Account name  |
      | John Doe  | Something     |
    And I press "Save" button
    Then Account "Something" is visible in the Account dropdown
    And I press "Save And Close" button
    And I see the following on Customers view:
      | Name            | Account   |
      | Magento Company | Something |


# Req ID CRM-6111-8
    Scenario: Checking that the "Account" column is available on Customers grid and is editable inline
    Given I go to Customers/ Customers
    When I observe the Customers grid
    Then I see that the "Account" column is visible
    And when I click on the Account field for "Magento Company" customer
    And I select another <Account>
    And I confirm changes by pressing "Save changes" icon for that field
    Then I see <Account> on the Account field for "Magento Company" customer
    Examples:
      | Account       |
      | Wholesaler B  |


    Scenario: Checking that the user can change Account when editing the Customer
    Given I click "Magento Company" customer on customer grid
    And I press "Edit"
    And I select <Another Account> from the Account dropdown
    And I press "Save And Close"
    Then I should see <Another Account> on the Customer view
    Examples:
      | Another Account |
      | Daddy's Pie     |

# Req ID CRM-6111-9, CRM-6111-10, CRM-6111-11, Req ID CRM-6111-12, Req ID CRM-6111-13
# Req ID CRM-6111-14,
    Scenario: Checking that the user can view customer details in "OroCommerce" section on the "Account" view
    Given I go to Customers/ Accounts
    And I click on <Account> on Account grid
    And Account view is displayed
    Then I see that "Commerce Customers" section is available on the "Account" view
    And I can see the following tabs in "Commerce Customers" section on Account view:
    | Tabs                |
    | Customer Users      |
    | Shopping Lists      |
    | Shopping Lists      |
    | Requests For Quote  |
    | Quotes              |
    | Orders              |
    | Opportunities       |
    And I can see that <Customer Users> are displayed by tabs
    And when I click <Customer Users> on tab
    Then I see <Customer Users> Customer view
     Examples:
      | Account      | Customer Users  |
      | Daddy's Pie  | Marlene Bradley |

 # req ID Req ID CRM-6111-4
  Scenario: Creating CRM Accounts when creating new Commerce Customer from front-end
    Given I go to the front-end
    And I click "Sign In" link
    And I click "Create An Account" link
    And I should not see the "Account" field
    And I fill out the details of account with the following:
      | Company Name  | First Name  | Last Name | Email Address     | Password  | Confirm Password  |
      | Toysrus       | Angela      | Davis     | admin@toysrus.com | toysrus   | toysrus           |
    And I press "Create An Account" button
    Then I should see "Please check your email to complete registration" message
    And when I log in as Administrator
    And I go to Customers/ Accounts
    Then I should see Toysrus account on Accounts grid
    And when I go to Customers/ Customers
    Then I should see Toysrus customer on Customer grid
    And when I click on Toysrus customer on Customer grid
    Then I should see Toysrus account on General Information tab on Toysrus customer view

  Scenario: Logging activities for account
    When I go to Customers/ Accounts
    And I select Toysrus account on Accounts grid
    And I select "Add event" from "More Actions" dropdown
    And I fill out the title "Toysrus event"
    And I press "Save"
    Then I should see calendar event "Toysrus event" on Activity tab
