@not-automated
Feature: In order to indicate the end point of a sale within an account
  As an Administrator
  I want to create Opportunities with Commerce Customers

Background:
  Given a commerce customer named "Partner C"
  And an account named "Account G"
  And an owner named "John Doe"
  And the base currency "USD"

Scenario: Create Opportunity and relate existing customer to it by pressing hamburger button

  Given I go to Sales/ Opportunities
  And I press “Create Opportunity” button. “Create Opportunity” view page is displayed
  And I start filling out all mandatory fields with the following data:
    | Owner     | Opportunity Name    | Status  |
    | John Doe  | Prospective Andrew  | Open    |
  And I press “hamburger" button on “Account” field
  And “Account” popup will be displayed
  And I select “Partner C” customer
  Then “Partner C (Customer)” should be visible in the “Account” field
  When I press “Save And Close” button
  Then the following data is displayed on Opportunity view page:
    | Opportunity Name    | Status  | Channel         | Account               |
    | Prospective Andrew  | Open    | Magento channel | Partner C (Partner C) |
  When I press “Edit” button
  Then the data for Prospective Andrew on the Opportunity Edit page should be the following:
    | Opportunity Name    | Status  | Channel         | Account               |
    | Prospective Andrew  | Open    | Magento channel | Partner C (Customer)  |
  And when I go to Sales/ Opportunities
  Then I should see on Opportunities grid:
    | Opportunity Name    | Status  |
    | Prospective Andrew  | Open    |
  And when I click on  Prospective Andrew opportunity
  Then Prospective Andrew opportunity view is displayed
  And when I click Partner C Account
  Then I should see Partner C account view
  And I should see “Commerce Customers” subsection on Partner C account view
  And when I click “Opportunities” tab at “Commerce Customers” subsection
  Then I should see
    | Opportunity Name    | Status  | Owner     |
    | Prospective Andrew  | Open    | John Doe  |
  And when I click on Partner C Customer at Commerce Customer subsection at Account view page
  Then I should see the following data in Opportunities subsection at Customer view page
    | Opportunity Name    | Status  | Owner     |
    | Prospective Andrew  | Open    | John Doe  |

Scenario: Create Opportunity, create new commerce customer and relate opportunity to it
  Given I go to Sales/ Opportunities
  And I press "Create Opportunity" button
  And I press "+" button at "Account" field
  And I click on "Customer"
  Then "Create Customer" popup is displayed
  And I fill out the form with the following data:
    | Owner     | Name               |  Account   |
    | John Doe  | Customer Creation  |  Account G |
  And I press "Save" button
  Then I should see "Customer Creation (Customer)" in Account field
  And I fill out other mandatory fields with the following:
    | Owner     | Opportunity Name  | Channel         | Status  |
    | John Doe  | Prospective Kate  | Magento Channel | Open    |
  And I press "Save And Close" button
  Then I should see the following on Opportunity view page:
    | Opportunity Name  | Status  | Channel         | Account                       |
    | Prospective Kate  | Open    | Magento channel | Account G (Customer Creation) |
  When I press “Edit” button
  Then the data for Prospective Kate on the Opportunity Edit page should be the following:
    | Opportunity Name  | Status |  Channel         | Account                       |
    | Prospective Kate  | Open   |  Magento channel | Customer Creation (Customer)  |
  And when I go to Sales/ Opportunities
  Then I should see on Opportunities grid:
    | Opportunity Name  | Status  |
    | Prospective Kate  | Open    |
  And when I click on "Prospective Kate" opportunity
  Then Prospective Kate opportunity view is displayed
  And when I click "Account G" Account
  Then I should see "Account G" account view
  And I should see “Commerce Customers” subsection on "Account G" account view
  And when I click on "Customer Creation" tab at “Commerce Customers” subsection
  And when I click on “Opportunities” tab
  Then I should see
    | Opportunity Name  | Status  | Owner     |
    | Prospective Kate  | Open    | John Doe  |
  And when I click on "Customer Creation" Customer at Commerce Customer subsection at Account view page
  Then I should see the following data in Opportunities subsection at Customer view page
  | Opportunity Name  | Status  | Owner     |
  | Prospective Kate  | Open    | John Doe  |


Scenario: Create a new commerce customer, edit an existing opportunity and re-relate it to the new customer
  Given I go to Customers/ Customers
  And I press "Create Customer" button
  And I fill out "Create Customer" form with the following data:
    | Owner     | Name        | Account   |
    | john Doe  | Customer 13 | Account G |
  And I press "Save And Close" button
  Then I should see the following data on the Customer view page:
    | Name        | Account   |
    | Customer 13 | Account G |
  When I go to Sales/ Opportunities
  And I click "Prospective Kate" on the Opportunity grid
  And I click "Edit" button
  And I click the "hamburger" button
  Then "Account" popup is displayed
  And when I click on "Customer 13" customer
  Then I should see "Customer 13 (Customer)" at "Account" field
  And when I click "Save And Close" button
  Then I should see the following on the Opportunity view page:
  | Opportunity Name  | Status  | Channel         | Account                 |
  | Prospective Kate  | Open    | Magento channel | Account G (Customer 13) |

Scenario: Create Opportunity report with budget more than $10,000.00
  Given I go to Sales/ Opportunities
  And I click "Prospective Kate" on the Opportunity grid
  And I click "Edit" button
  And I fill out the following:
    | Budget Amount |
    | 150000.00     |
  When I click "Save And Close" button
  Then I should see the following on the Opportunity view page:
    | Budget Amount |
    | $150,000.00   |
  When I go to Reports&Segments
  And I click "Manage Custom Reports"
  And I click "Create Report" button
  And I fill out the report form with the following data:
    | Name                | Entity      | Report Type |
    | Opportunity budget  | Opportunity | Table       |
  And I add the following columns:
    | Columns                           |
    | Opportunity / Customer > Name     |
    | Opportunity / Customer > ID       |
    | Opportunity > Base Budget Amount  |
  And I press "Save And Close" button
  Then I should see the following on the "Report Opportunity" grid:
    | Name        | Base Budget Amount  |
    | Partner C   |                     |
    | Customer 13 | $150,000.00         |
  And when I click on "Base Budget Amount: All" dropdown
  And I input:
    | More than |
    | 10000     |
  And I press "Update" button
  Then I should see the following on the "Report Opportunity" grid:
    | Name        | Base Budget Amount  |
    | Customer 13 | $150,000.00         |


