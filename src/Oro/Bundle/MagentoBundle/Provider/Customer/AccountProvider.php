<?php

namespace Oro\Bundle\MagentoBundle\Provider\Customer;

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
            // get account from direct relation if it is already set in b2b customer
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

            // create new Account
            $accountName = !$targetCustomer->getFirstName() && !$targetCustomer->getLastName()
                ? 'N/A'
                : sprintf('%s %s', $targetCustomer->getFirstName(), $targetCustomer->getLastName());

            $account = (new Account())->setName($accountName);
        }

        if ($targetCustomer->getId()) {
            $this->newEntitiesHelper->setEntity(sprintf($newAccountKey, $targetCustomer->getId()), $account);
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
}
