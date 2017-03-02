<?php

namespace Oro\Bundle\MagentoBundle\Tests\Behat\Context;

use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behat\Symfony2Extension\Context\KernelDictionary;

use Doctrine\Common\Inflector\Inflector;
use Doctrine\ORM\EntityManager;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\DataGridBundle\Tests\Behat\Element\Grid;
use Oro\Bundle\DataGridBundle\Tests\Behat\Element\GridRow;
use Oro\Bundle\EmbeddedFormBundle\Entity\EmbeddedForm;
use Oro\Bundle\FormBundle\Tests\Behat\Element\Select2Entity;
use Oro\Bundle\LocaleBundle\Model\NameInterface;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\SalesBundle\Entity\B2bCustomer;
use Oro\Bundle\TestFrameworkBundle\Behat\Context\OroFeatureContext;
use Oro\Bundle\TestFrameworkBundle\Behat\Element\OroPageObjectAware;
use Oro\Bundle\TestFrameworkBundle\Behat\Fixtures\FixtureLoaderAwareInterface;
use Oro\Bundle\TestFrameworkBundle\Behat\Fixtures\FixtureLoaderDictionary;
use Oro\Bundle\TestFrameworkBundle\Tests\Behat\Context\PageObjectDictionary;
use Oro\Bundle\UIBundle\Tests\Behat\Element\ContextSelector;
use Oro\Bundle\UIBundle\Tests\Behat\Element\UiDialog;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\SalesBundle\Tests\Behat\Context\SalesExtension;

use Symfony\Component\Console\Exception\RuntimeException;

class FeatureContext extends OroFeatureContext implements
    FixtureLoaderAwareInterface,
    OroPageObjectAware,
    KernelAwareContext
{
    use FixtureLoaderDictionary, PageObjectDictionary, KernelDictionary;

    /**
     * Load "second_sales_channel.yml" alice fixture
     *
     * @Given CRM has second sales channel with Accounts and Magento Customers
     */
    public function crmHasSecondSalesChannel()
    {
        $this->fixtureLoader->loadFixtureFile('second_sales_channel.yml');
    }

    /**
     * Example: And Accounts in the control are filtered according to samantha ACL permissions
     *
     * @Then Accounts in the control are filtered by selected sales channel and :username ACL permissions
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

    //@codingStandardsIgnoreStart
    /**
     * Example: And Accounts in the control are filtered according to samantha ACL permissions
     * Example: Then Magento Customers in the control are filtered according to samantha ACL permissions
     *
     * Get accounts and customers from database according to user parmissions and compare its with list of
     *  accounts from "Account" field in entity edit page
     *
     * @Then /^(?P<accountType>(Accounts|Magento Customers)) in the control are filtered according to (?P<username>(\w+)) ACL permissions$/
     */
    //@codingStandardsIgnoreEnd
    public function accountsInTheControlAreFilteredAccordingToUserAclPermissions($accountType, $username)
    {
        $doctrine = $this->getContainer()->get('oro_entity.doctrine_helper');
        $owner = $doctrine->getEntityRepositoryForClass(User::class)->findOneBy(['username' => $username]);

        /** @var Select2Entity $accountField */
        $accountField = $this->createElement('OroForm')->findField('Account');
        /** @var UiDialog $popup */
        $popup = $accountField->openSelectEntityPopup();

        /** @var ContextSelector $contextSelector */
        $contextSelector = $this->createElement('ContextSelector');
        $contextSelector->select(Inflector::singularize($accountType));

        /** @var Grid $grid */
        $grid = $this->createElement('Grid', $popup);

        if ('Accounts' == $accountType) {
            $ownAccounts = array_merge(
                $doctrine->getEntityRepositoryForClass(Account::class)->findBy(['owner' => $owner]),
                $doctrine->getEntityRepositoryForClass(B2bCustomer::class)->findBy(['owner' => $owner])
            );
            $accountsInGrid = array_map(function (GridRow $row) {
                return $row->getCellValue('Account name');
            }, $grid->getRows());
            array_walk($ownAccounts, function (NameInterface &$element) {
                $element = $element->getName();
            });
        } elseif ('Magento Customers' == $accountType) {
            $ownAccounts = $doctrine->getEntityRepositoryForClass(Customer::class)->findBy(['owner' => $owner]);
            $accountsInGrid = array_map(function (GridRow $row) {
                return $row->getCellValue('First Name').' '.$row->getCellValue('Last Name');
            }, $grid->getRows());
            array_walk($ownAccounts, function (Customer &$element) {
                $element = $element->getFirstName().' '.$element->getLastName();
            });
        } else {
            throw new RuntimeException(sprintf('Unsupported "%s" account type', $accountType));
        }

        $popup->close();

        sort($ownAccounts);
        sort($accountsInGrid);

        self::assertEquals($ownAccounts, $accountsInGrid);
    }

    /**
     * @Given I am on Submit Magento contact us form page
     */
    public function iAmOnSubmitMagentoContactUsFormPage()
    {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager();
        $repository = $em->getRepository('OroEmbeddedFormBundle:EmbeddedForm');
        /** @var EmbeddedForm $contactUsForm */
        $contactUsForm = $repository->findOneBy(['title' => 'Magento contact us form']);
        $uri = $this->getContainer()->get('router')->generate(
            'oro_embedded_form_submit',
            ['id' => $contactUsForm->getId()]
        );
        $this->visitPath($uri);
    }

    /**
     * @Given I am on Contact Requests page
     */
    public function iAmContactRequestsPage()
    {
        $uri = $this->getContainer()->get('router')->generate('oro_contactus_request_index');
        $this->visitPath($uri);
    }
}
