# Magento integration is disabled in CRM-9202
@skip
@ticket-BAP-18682
Feature: Saving integration form
  In order to create Magento integration channel
  As an administrator
  I need to be able to save integration form

  Scenario: Create Magento integration channel and fill and save integration form
    Given I login as administrator
    And I go to System/ Channels
    And I click "Create Channel"
    And I fill form with:
      | Name         | Magento channel |
      | Channel type | Magento         |
    And I click "Configure integration"
    And I fill form with:
      | Name            | Magento channel Integration |
      | SOAP WSDL URL   | URL                         |
      | SOAP API User   | User                        |
      | SOAP API Key    | Key                         |
    And I scroll to "Integration Website"
    And I click "Check connection"
    Then I should see "Connection was successful."
    Given I fill form with:
      | Website | Website 1 |
    And I click "Done"
    Then I should not see an "Configure Integration Dialog" element
    And I should see "Magento channel Integration (edit)"
