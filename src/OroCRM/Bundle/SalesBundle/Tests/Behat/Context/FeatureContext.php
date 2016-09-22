<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Behat\Context;

use Behat\Mink\Element\NodeElement;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Doctrine\ORM\EntityRepository;
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
use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;
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
     * @Then /^Accounts and Customers in the control are filtered according to (?P<user>(\w+)) ACL permissions$/
     */
    public function accountsInTheControlAreFilteredAccordingToUserAclPermissions($username)
    {
        $doctrine = $this->getContainer()->get('oro_entity.doctrine_helper');
        $owner = $doctrine->getEntityRepositoryForClass(User::class)->findOneBy(['username' => $username]);
        $ownAccounts = $doctrine->getEntityRepositoryForClass(B2bCustomer::class)->findBy(['owner' => $owner]);

        /** @var Select2Entity $accountField */
        $accountField = $this->createElement('OroForm')->findField('Account');
        $visibleAccounts = $accountField->getSuggestedValues();

        self::assertCount(count($ownAccounts), $visibleAccounts);

        /** @var B2bCustomer $account */
        foreach ($ownAccounts as $account) {
            $value = sprintf('%s (%s)', $account->getName(), $account->getAccount()->getName());
            self::assertContains($value, $visibleAccounts);
        }
    }

    /**
     * @Given CRM has second sales channel with Accounts and Business Customers
     */
    public function crmHasSecondSalesChannel()
    {
        $this->fixtureLoader->loadFixtureFile('second_sales_channel.yml');
    }

    /**
     * @Then Accounts and Customers in the control are filtered by selected sales channel and :username ACL permissions
     */
    public function accountsInTheControlAreFilteredBySelected($username)
    {
        $doctrine = $this->getContainer()->get('oro_entity.doctrine_helper');
        $user = $doctrine->getEntityManagerForClass(User::class)->getRepository(User::class)
            ->findOneBy(['username' => $username]);
        $channelRepository = $doctrine->getEntityManagerForClass(Channel::class)->getRepository(Channel::class);
        $customerRepository = $doctrine->getEntityManagerForClass(B2bCustomer::class)
            ->getRepository(B2bCustomer::class);

        /** @var Select2Entity $channelField */
        $channelField = $this->createElement('OroForm')->findField('Channel');
        $channels = $channelField->getSuggestedValues();

        foreach ($channels as $channelName) {
            $channelField->setValue($channelName);
            $channel = $channelRepository->findOneBy(['name' => $channelName]);

            $expectedCustomers = $this->getCustomers($channel, $customerRepository, $user);

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
     * @Given Account Name is equal to Business Customer name
     */
    public function accountNameEqualToBusinessCustomer()
    {
        $this->fixtureLoader->loadFixtureFile('account_name_equal_to_business_customer_name.yml');
    }

    /**
     * @Then /^I see only Account name in Account\/Customer field choice$/
     */
    public function iSeeAccountNameOnly()
    {
        /** @var Select2Entity $accountField */
        $accountField = $this->createElement('OroForm')->findField('Account');
        $actualCustomers = $accountField->getSuggestedValues();

        self::assertContains('Samantha Customer', $actualCustomers);
        self::assertNotContains('Samantha Customer (Samantha Customer)', $actualCustomers);
    }

    /**
     * @Given Account :name has no customers
     */
    public function accountHasNoCustomers($name)
    {
        $this->fixtureLoader->load([
            Account::class => [
                uniqid('account_', true) => [
                    'name' => $name,
                    'owner' => '@samantha',
                    'organization' => '@organization'
                ]
            ]
        ]);
    }

    /**
     * @When I select :name
     */
    public function selectAccount($name)
    {
        /** @var Select2Entity $accountField */
        $accountField = $this->createElement('OroForm')->findField('Account');
        $accountField->open();
        /** @var NodeElement[] $inputs */
        $inputs = array_filter(
            $this->getPage()->findAll('css', '.select2-search input'),
            function (NodeElement $element) {
                return $element->isVisible();
            }
        );

        self::assertCount(1, $inputs);
        array_shift($inputs)->setValue($name);
        $results = $accountField->getSuggestions();
        foreach ($results as $result) {
            if (false !== stripos($result->getText(), $name)) {
                $result->click();

                return;
            }
        }
        self::fail('Not found account in suggested variants');
    }

    /**
     * @Then :content Customer was created
     */
    public function customerWasCreated($content)
    {
        /** @var MainMenu $menu */
        $menu = $this->createElement('MainMenu');
        $menu->openAndClick('Customers/ Business Customers');
        $this->waitForAjax();

        $row = $this->elementFactory
            ->findElementContains('Grid', $content)
            ->findElementContains('GridRow', $content);

        self::assertTrue($row->isValid(), "Can't find '$content' in grid");
    }


    /**
     * @param Channel $channel
     * @param EntityRepository $customerRepository
     * @return array
     */
    private function getCustomers(Channel $channel, EntityRepository $customerRepository, User $user)
    {
        $customers = [];

        /** @var B2bCustomer $customer */
        foreach ($customerRepository->findBy(['owner' => $user, 'dataChannel' => $channel]) as $customer) {
            $customers[] = sprintf('%s (%s)', $customer->getName(), $customer->getAccount()->getName());
        }

        return $customers;
    }
}
