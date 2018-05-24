<?php

namespace Oro\Bundle\MagentoBundle\Provider\Customer;

use Doctrine\ORM\Mapping\ClassMetadataInfo;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\ImportExportBundle\Strategy\Import\NewEntitiesHelper;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Service\AutomaticDiscovery;
use Oro\Bundle\SalesBundle\Provider\Customer\AccountCreation\AccountProviderInterface;

class AccountProvider implements AccountProviderInterface, ContainerAwareInterface
{
    /** @var ContainerInterface */
    protected $container;

    /** @var AutomaticDiscovery */
    protected $automaticDiscovery;

    /** @var NewEntitiesHelper */
    protected $newEntitiesHelper;

    /** @var int */
    private $accountNameLength;

    /**
     * @param NewEntitiesHelper $newEntitiesHelper
     */
    public function __construct(NewEntitiesHelper $newEntitiesHelper)
    {
        $this->newEntitiesHelper = $newEntitiesHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getAccount($targetCustomer)
    {
        if (!$targetCustomer instanceof Customer) {
            return null;
        }
        $newAccountKey = 'magentocustomer_%s_account';
        if ($targetCustomer->getAccount()) {
            // get account from direct relation if it is already set in Magento customer
            $account = $targetCustomer->getAccount();
        } else {
            // try to find similar customer and get its account
            /** @var Customer|null $similar */
            $automaticDiscovery = $this->getAutomaticDiscovery();
            $similar            = $automaticDiscovery->discoverSimilar($targetCustomer);

            if (null !== $similar) {
                if ($similar->getAccount()) {
                    return $similar->getAccount();
                }
                //try to get from storage
                $key             = sprintf($newAccountKey, $similar->getId());
                $storedAccount   = $this->newEntitiesHelper->getEntity($key);
                if ($storedAccount) {
                    return $storedAccount;
                }
            }

            $account = $this->createAccount($targetCustomer);
        }

        if ($targetCustomer->getId()) {
            $this->newEntitiesHelper->setEntity(sprintf($newAccountKey, $targetCustomer->getId()), $account);
        }

        return $account;
    }

    /**
     * Create new Account from customer data
     *
     * @param $targetCustomer
     *
     * @return Account
     */
    protected function createAccount($targetCustomer)
    {
        $account = new Account();
        $account->setName($this->getAccountName($targetCustomer))
            ->setOwner($targetCustomer->getOwner())
            ->setOrganization($targetCustomer->getOrganization());

        $contact = $targetCustomer->getContact();
        if ($contact) {
            $account->setDefaultContact($contact);
        }

        return $account;
    }

    /**
     * @return AutomaticDiscovery
     */
    protected function getAutomaticDiscovery()
    {
        if (null === $this->automaticDiscovery) {
            $this->automaticDiscovery = $this->container->get('oro_magento.service.automatic_discovery');
        }

        return $this->automaticDiscovery;
    }

    /**
     * @param Customer $targetCustomer
     * @return string
     */
    private function getAccountName(Customer $targetCustomer)
    {
        $accountName = 'N/A';
        if ($targetCustomer->getFirstName() || $targetCustomer->getLastName()) {
            $accountName = sprintf('%s %s', $targetCustomer->getFirstName(), $targetCustomer->getLastName());
            $accountName = mb_substr(trim($accountName), 0, $this->getAccountNameLength());
        }

        return $accountName;
    }

    /**
     * @return int|null
     */
    private function getAccountNameLength()
    {
        if ($this->accountNameLength === null) {
            $manager = $this->container->get('doctrine')->getManagerForClass(Account::class);

            /** @var ClassMetadataInfo $metadata */
            $metadata = $manager->getClassMetadata(Account::class);
            $nameMetadata = $metadata->getFieldMapping('name');

            $this->accountNameLength = (int)$nameMetadata['length'];
        }

        return $this->accountNameLength;
    }
}
