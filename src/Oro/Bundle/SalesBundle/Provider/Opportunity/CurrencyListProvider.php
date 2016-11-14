<?php

namespace Oro\Bundle\SalesBundle\Provider\Opportunity;

use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Symfony\Bridge\Doctrine\RegistryInterface;

use Oro\Bundle\CurrencyBundle\Provider\RepositoryCurrencyProviderInterface;

class CurrencyListProvider implements RepositoryCurrencyProviderInterface
{
    /**
     * @var RegistryInterface
     */
    protected $doctrine;

    public function __construct(RegistryInterface $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @inheritdoc
     */
    public function getCurrencyList(Organization $organization = null)
    {
        $opportunityRepository = $this->doctrine->getRepository('OroSalesBundle:Opportunity');
        return $opportunityRepository->getCurrencyListFromMulticurrencyFields($organization);
    }
}
