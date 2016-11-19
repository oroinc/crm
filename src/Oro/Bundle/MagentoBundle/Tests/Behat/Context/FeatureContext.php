<?php

namespace Oro\Bundle\MagentoBundle\Tests\Behat\Context;

use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Oro\Bundle\FormBundle\Tests\Behat\Element\Select2Entity;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\TestFrameworkBundle\Behat\Context\OroFeatureContext;
use Oro\Bundle\TestFrameworkBundle\Behat\Element\OroPageObjectAware;
use Oro\Bundle\TestFrameworkBundle\Behat\Fixtures\FixtureLoaderAwareInterface;
use Oro\Bundle\TestFrameworkBundle\Behat\Fixtures\FixtureLoaderDictionary;
use Oro\Bundle\TestFrameworkBundle\Tests\Behat\Context\PageObjectDictionary;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\SalesBundle\Tests\Behat\Context\SalesExtension;

class FeatureContext extends OroFeatureContext implements
    FixtureLoaderAwareInterface,
    OroPageObjectAware,
    KernelAwareContext
{
    use FixtureLoaderDictionary, PageObjectDictionary, KernelDictionary, SalesExtension;

    /**
     * @Given they has their own Accounts and Customers
     */
    public function accountHasBusinessCustomers()
    {
        $this->fixtureLoader->loadFixtureFile('accounts_with_customers.yml');
    }

    /**
     * @Given CRM has second sales channel with Accounts and Magento Customers
     */
    public function crmHasSecondSalesChannel()
    {
        $this->fixtureLoader->loadFixtureFile('second_sales_channel.yml');
    }

    /**
     * @Given crm has (Acme) Account with (Charlie) and (Samantha) customers
     */
    public function crmHasAcmeAccountWithCharlieAndSamanthaCustomers()
    {
        $this->fixtureLoader->loadFixtureFile('account_with_customers.yml');
    }

    /**
     * @Then /^Accounts and Customers in the control are filtered according to (?P<user>(\w+)) ACL permissions$/
     */
    public function accountsInTheControlAreFilteredAccordingToUserAclPermissions($username)
    {
        $doctrine = $this->getContainer()->get('oro_entity.doctrine_helper');
        $owner = $doctrine->getEntityRepositoryForClass(User::class)->findOneBy(['username' => $username]);
        $ownAccounts = $doctrine->getEntityRepositoryForClass(Customer::class)->findBy(['owner' => $owner]);

        /** @var Select2Entity $accountField */
        $accountField = $this->createElement('OroForm')->findField('Account');
        $visibleAccounts = $accountField->getSuggestedValues();

        self::assertCount(count($ownAccounts), $visibleAccounts);

        /** @var Customer $account */
        foreach ($ownAccounts as $account) {
            $value = sprintf('%s %s (Magento Customer)', $account->getFirstName(), $account->getLastName());
            self::assertContains($value, $visibleAccounts);
        }
    }
}
