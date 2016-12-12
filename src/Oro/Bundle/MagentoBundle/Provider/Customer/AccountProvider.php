<?php

namespace Oro\Bundle\MagentoBundle\Provider\Customer;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Service\AutomaticDiscovery;
use Oro\Bundle\SalesBundle\Provider\Customer\AccountCreation\AccountProviderInterface;

class AccountProvider implements AccountProviderInterface, ContainerAwareInterface
{
    /** @var ContainerInterface */
    protected $container;

    /** @var AutomaticDiscovery */
    protected $automaticDiscovery;

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
    public function provideAccount($entity)
    {
        if (!$entity instanceof Customer) {
            return null;
        }
        /** @var Customer|null $similar */
        $similar = $this->getAutomaticDiscovery()->discoverSimilar($entity);

        if (null === $similar) {
            return null;
            //@TODO Do we need to create account in the same way as was in process?
        }
        //@TODO need to find a way to provide accounts created for MC in the one batch
        return $similar->getAccount();
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
