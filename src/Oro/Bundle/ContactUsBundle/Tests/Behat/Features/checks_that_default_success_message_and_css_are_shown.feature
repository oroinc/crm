Feature: Checks that default success message and css are shown
  In order to manager Embedded forms
  As an Administrator
  I want to see default success message and default css from Embedded FormType in form

  Scenario: Show default data
    Given I login as administrator
    And I go to System/ Integrations/ Embedded Forms
    And I press "Create Embedded Form"
    When I select "Contact Request" from "Form Type"
    Then the "CSS" field should not contain ""
    And the "Success message" field should not contain ""
