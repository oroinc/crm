<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Behat\Context;

use Behat\Mink\Element\NodeElement;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Oro\Bundle\DataGridBundle\Tests\Behat\Element\Grid;
use Oro\Bundle\FormBundle\Tests\Behat\Element\OroForm;
use Oro\Bundle\FormBundle\Tests\Behat\Element\Select2Entity;
use Oro\Bundle\NavigationBundle\Tests\Behat\Element\MainMenu;
use Oro\Bundle\TestFrameworkBundle\Behat\Context\OroFeatureContext;
use Oro\Bundle\TestFrameworkBundle\Behat\Element\OroElementFactoryAware;
use Oro\Bundle\TestFrameworkBundle\Behat\Fixtures\FixtureLoaderAwareInterface;
use Oro\Bundle\TestFrameworkBundle\Behat\Fixtures\FixtureLoaderDictionary;
use Oro\Bundle\TestFrameworkBundle\Tests\Behat\Context\ElementFactoryDictionary;
use Oro\Bundle\UserBundle\Entity\User;
use OroCRM\Bundle\SalesBundle\Entity\B2bCustomer;

class FeatureContext extends OroFeatureContext implements
    FixtureLoaderAwareInterface,
    OroElementFactoryAware,
    KernelAwareContext
{
    use FixtureLoaderDictionary, ElementFactoryDictionary, KernelDictionary;

    /**
     * @Given /^"(?P<channelName>([\w\s]+))" is a channel with enabled (?P<entities>(.+)) entities$/
     */
    public function createChannelWithEnabledEntities($channelName, $entities)
    {
        /** @var MainMenu $menu */
        $menu = $this->createElement('MainMenu');
        $menu->openAndClick('System/ Channels');
        $this->waitForAjax();
        $this->getPage()->clickLink('Create Channel');
        $this->waitForAjax();

        /** @var OroForm $form */
        $form = $this->createElement('OroForm');
        $form->fillField('Name', $channelName);
        $form->fillField('Channel Type', 'Sales');
        $this->waitForAjax();

        /** @var Grid $grid */
        $grid = $this->createElement('Grid');
        $channelEntities = array_map('trim', explode(',', $entities));
        $rowsForDelete = [];

        foreach ($grid->getRows() as $row) {
            foreach ($channelEntities as $key => $channelEntity) {
                if (false !== stripos($row->getText(), $channelEntity)) {
                    unset($channelEntities[$key]);
                    continue 2;
                }
            }

            $rowsForDelete[] = $row;
        }

        foreach ($rowsForDelete as $row) {
            $grid->getActionLink('Delete', $row)->click();
        }

        $entitySelector = $this->elementFactory->findElementContains('EntitySelector', 'Please select entity');

        foreach ($channelEntities as $channelEntity) {
            $entitySelector->click();
            $this->elementFactory->findElementContains('SelectToResultLabel', $channelEntity)->click();
            $this->getPage()->clickLink('Add');
        }

        $form->saveAndClose();
    }

    /**
     * @Given they has their own Accounts and Business Customers
     */
    public function accountHasBusinessCustomers()
    {
        $this->fixtureLoader->loadFixtureFile('accounts_with_customers.yml');
    }

    /**
     * @Given /^two users (?P<user1>(\w+)) and (?P<user2>(\w+)) exists in the system$/
     */
    public function twoUsersExistsInTheSystem()
    {
        $this->fixtureLoader->loadFixtureFile('users.yml');
    }

    /**
     * @Then /^Accounts in the control are filtered according to (?P<user>(\w+)) ACL permissions$/
     */
    public function accountsInTheControlAreFilteredAccordingToUserAclPermissions($username)
    {
        $doctrine = $this->getContainer()->get('oro_entity.doctrine_helper');
        $owner = $doctrine->getEntityRepositoryForClass(User::class)->findOneBy(['username' => $username]);
        $ownAccounts = $doctrine->getEntityRepositoryForClass(B2bCustomer::class)->findBy(['owner' => $owner]);

        /** @var Select2Entity $accountField */
        $accountField = $this->createElement('OroForm')->findField('Account');
        $visibleAccounts = array_map(
            function (NodeElement $element) {
                return $element->getText();
            },
            $accountField->getSuggestedValues()
        );

        self::assertCount(count($ownAccounts), $visibleAccounts);

        /** @var B2bCustomer $account */
        foreach ($ownAccounts as $account) {
            $value = sprintf('%s (%s)', $account->getName(), $account->getAccount()->getName());
            self::assertContains($value, $visibleAccounts);
        }
    }
}
