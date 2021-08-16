<?php

namespace Oro\Bundle\SalesBundle\Tests\Behat\Context;

use Behat\Gherkin\Node\TableNode;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ConfigBundle\Tests\Behat\Element\SidebarConfigMenu;
use Oro\Bundle\DataGridBundle\Tests\Behat\Element\Grid;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\FormBundle\Tests\Behat\Element\Select2Entity;
use Oro\Bundle\NavigationBundle\Tests\Behat\Element\MainMenu;
use Oro\Bundle\SalesBundle\Entity\B2bCustomer;
use Oro\Bundle\SalesBundle\Tests\Behat\Element\OpportunityProbabilitiesConfigForm;
use Oro\Bundle\TestFrameworkBundle\Behat\Context\OroFeatureContext;
use Oro\Bundle\TestFrameworkBundle\Behat\Element\OroPageObjectAware;
use Oro\Bundle\TestFrameworkBundle\Behat\Fixtures\FixtureLoaderAwareInterface;
use Oro\Bundle\TestFrameworkBundle\Behat\Fixtures\FixtureLoaderDictionary;
use Oro\Bundle\TestFrameworkBundle\Tests\Behat\Context\PageObjectDictionary;
use Oro\Bundle\UserBundle\Entity\User;

class FeatureContext extends OroFeatureContext implements
    FixtureLoaderAwareInterface,
    OroPageObjectAware
{
    use FixtureLoaderDictionary, PageObjectDictionary;

    /**
     * This is change the current page context
     * Go to 'Customers/ Business Customers' and assert row with given content
     * Example: Then "Absolute new account" Customer was created
     *
     * @Then :content Customer was created
     */
    public function customerWasCreated($content)
    {
        /** @var MainMenu $menu */
        $menu = $this->createElement('MainMenu');
        $menu->openAndClick('Customers/ Business Customers');
        $this->waitForAjax();

        $this->assertRowInGrid($content);
    }

    /**
     * This is change the current page context
     * Go to 'Customers/ Accounts' and assert row with given content
     * Example: Then "Absolute new account" Account was created
     *
     * @Then :content Account was created
     */
    public function accountWasCreated($content)
    {
        /** @var MainMenu $menu */
        $menu = $this->createElement('MainMenu');
        $menu->openAndClick('Customers/ Accounts');
        $this->waitForAjax();

        $this->assertRowInGrid($content);
    }

    /**
     * @param string $content
     */
    private function assertRowInGrid($content)
    {
        $row = $this->elementFactory
            ->findElementContains('Grid', $content)
            ->findElementContains('GridRow', $content);

        self::assertTrue($row->isValid(), "Can't find '$content' in grid");
    }

    /**
     * Type some text in field with "Account" label
     * It is used for assert search suggestions for field
     * Example: When type "Non Existent Account" into Account field
     *          Then I should see only existing accounts
     * In this example only one existing account shown or user has permissions for create new (Add new)
     *
     * @When type :text into Account field
     */
    public function iTypeIntoAccountField($text)
    {
        /** @var Select2Entity $accountField */
        $accountField = $this->createElement('OroForm')->findField('Account');
        $accountField->fillSearchField($text);
    }

    /**
     * Get accounts for "samanta" user and 'First Sales Channel' channel from database
     *  and compare with suggestions from "Account" field
     * Example: When I fill in "Channel" with "First Sales Channel"
     *          And type "Non Existent Account" into Account field
     *          Then I should see only existing accounts
     *
     * @Then /^(?:|I )should see only existing accounts$/
     */
    public function iShouldSeeOnlyExistingAccounts()
    {
        $existingCustomers = $this->getCustomers('First Sales Channel', 'samantha');

        /** @var Select2Entity $accountField */
        $accountField = $this->createElement('OroForm')->findField('Account');
        $actualCustomers = $accountField->getSuggestedValues();

        self::assertEquals(
            sort($existingCustomers),
            sort($actualCustomers)
        );
    }

    /**
     * @param string $channelName
     * @param string $username
     * @return array
     */
    private function getCustomers($channelName, $username)
    {
        /** @var DoctrineHelper $doctrine */
        $doctrine = $this->getAppContainer()->get('oro_entity.doctrine_helper');
        $customerRepository = $doctrine->getEntityManagerForClass(B2bCustomer::class)
            ->getRepository(B2bCustomer::class);
        $channelRepository = $doctrine->getEntityManagerForClass(Channel::class)->getRepository(Channel::class);

        $user = $doctrine->getEntityManagerForClass(User::class)->getRepository(User::class)
            ->findOneBy(['username' => $username]);
        $channel = $channelRepository->findOneBy(['name' => $channelName]);

        $customers = [];

        /** @var B2bCustomer $customer */
        foreach ($customerRepository->findBy(['owner' => $user, 'dataChannel' => $channel]) as $customer) {
            $customers[] = sprintf('%s (%s)', $customer->getName(), $customer->getAccount()->getName());
        }

        return $customers;
    }

    /**
     * Open Opportunity index page
     *
     * @Given /^(?:|I )go to Opportunity Index page$/
     */
    public function iGoToOpportunityIndexPage()
    {
        /** @var MainMenu $mainMenu */
        $mainMenu = $this->createElement('MainMenu');
        $mainMenu->openAndClick("Sales/Opportunities");
    }

    /**
     * Example: Then Charlie customer has Opportunity one opportunity
     *
     * @Then /^(?P<customerName>[\w\s]+) customer has (?P<opportunityName>[\w\s]+) opportunity$/
     */
    public function customerHasOpportunity($customerName, $opportunityName)
    {
        /** @var MainMenu $mainMenu */
        $mainMenu = $this->createElement('MainMenu');
        $mainMenu->openAndClick('Customers/ Business Customers');
        $this->waitForAjax();

        /** @var Grid $grid */
        $grid = $this->createElement('Grid');
        self::assertTrue($grid->isValid(), 'Grid not found');
        $grid->clickActionLink($customerName, 'View');
        $this->waitForAjax();

        /** @var Grid $customerOpportunitiesGrid */
        $customerOpportunitiesGrid = $this->createElement('CustomerOpportunitiesGrid');
        $row = $customerOpportunitiesGrid->getRowByContent($opportunityName);

        self::assertTrue($row->isValid());
    }

    /**
     * Example: And CRM has next Opportunity Probabilities:
     *            | Status                     | Probability | Default |
     *            | Open                       | 5           |         |
     *            | Identification & Alignment | 20          |         |
     *            | Needs Analysis             | 10          | yes     |
     *
     * @Given CRM has next (Opportunity Probabilities):
     */
    public function crmHasNextOpportunityProbabilities(TableNode $table)
    {
        /** @var MainMenu $mainMenu */
        $mainMenu = $this->createElement('MainMenu');
        $mainMenu->openAndClick('System/ Configuration');
        $this->waitForAjax();

        /** @var SidebarConfigMenu $sidebarMenu */
        $sidebarMenu = $this->createElement('SidebarConfigMenu');
        $sidebarMenu->openNestedMenu('CRM/Sales Pipeline/Opportunity');
        $this->waitForAjax();

        /** @var OpportunityProbabilitiesConfigForm $form */
        $form = $this->createElement('OpportunityProbabilitiesConfigForm');
        $form->fill($table);
        $this->getSession()->getPage()->pressButton('Save settings');
    }

    /**
     * Example: And Opportunity Probability must comply to Status:
     *            | Status                     | Probability |
     *            | Open                       | 5           |
     *            | Identification & Alignment | 20          |
     *            | Solution Development       | 60          |
     *
     * @Then Opportunity (Probability) must comply to (Status):
     */
    public function opportunityProbabilityMustComplyToStatus(TableNode $table)
    {
        $form = $this->createElement('OroForm');

        foreach ($table as $item) {
            $form->fillField('Status', $item['Status']);
            $this->waitForAjax();
            self::assertEquals($item['Probability'], $form->findField('Probability')->getValue());
        }
    }

    /**
     * Press entity add button from plus dropdown
     * Example: Then I add new Business Customer for Account field
     *
     * @Then /^I add new (?P<entityName>[\w\s]+) for (?P<fieldName>[\w\s]+) field$/
     */
    public function iAddNewEntityForField($entityButtonName, $fieldName)
    {
        /** @var Select2Entity $field */
        $field = $this->createElement('OroForm')->findField($fieldName);
        $field->openFromPlusButtonDropDown($entityButtonName);
    }

    /**
     * Submit provided form.
     * Used when few forms present on page
     * Example: When I submit "Sales B2b Customer Form"
     *
     * @When /^(?:|I )submit "(?P<formName>(?:[^"]|\\")*)"$/
     * @param string $formName
     */
    public function iSubmitForm($formName = "OroForm")
    {
        $formName = str_replace(' ', '', $formName);
        $form = $this->createElement($formName);
        $form->submit();
    }
}
