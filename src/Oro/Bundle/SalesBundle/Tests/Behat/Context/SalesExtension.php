<?php

namespace Oro\Bundle\SalesBundle\Tests\Behat\Context;

use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\DataGridBundle\Tests\Behat\Element\Grid;
use Oro\Bundle\FormBundle\Tests\Behat\Element\OroForm;
use Oro\Bundle\FormBundle\Tests\Behat\Element\Select2Entity;
use Oro\Bundle\NavigationBundle\Tests\Behat\Element\MainMenu;
use Oro\Bundle\SalesBundle\Entity\B2bCustomer;
use Oro\Bundle\UserBundle\Entity\User;

trait SalesExtension
{
    /**
     * @Then Accounts and Customers in the control are filtered by selected sales channel and :username ACL permissions
     */
    public function accountsInTheControlAreFilteredBySelected($username)
    {
        /** @var Select2Entity $channelField */
        $channelField = $this->createElement('OroForm')->findField('Channel');
        $channels = $channelField->getSuggestedValues();

        foreach ($channels as $channelName) {
            $channelField->setValue($channelName);

            $expectedCustomers = $this->getCustomers($channelName, $username);

            /** @var Select2Entity $accountField */
            $accountField = $this->createElement('OroForm')->findField('Account');
            $actualCustomers = $accountField->getSuggestedValues();

            self::assertEquals(
                sort($expectedCustomers),
                sort($actualCustomers)
            );
        }
    }

    /**
     * @Given /^(?:|I )open (Opportunity) creation page$/
     */
    public function openOpportunityCreationPage()
    {
        /** @var MainMenu $menu */
        $menu = $this->createElement('MainMenu');
        $menu->openAndClick('Sales/ Opportunities');
        $this->waitForAjax();
        $this->getPage()->clickLink('Create Opportunity');
    }

    /**
     * @Given /^two users (?P<user1>(\w+)) and (?P<user2>(\w+)) exists in the system$/
     */
    public function twoUsersExistsInTheSystem()
    {
        $this->fixtureLoader->loadFixtureFile('users.yml');
    }

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
     * @param string $channelName
     * @param string $username
     * @return array
     */
    private function getCustomers($channelName, $username)
    {
        $doctrine = $this->getContainer()->get('oro_entity.doctrine_helper');
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
}
