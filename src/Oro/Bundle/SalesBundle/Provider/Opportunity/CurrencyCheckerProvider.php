<?php

namespace Oro\Bundle\SalesBundle\Provider\Opportunity;

use Symfony\Bridge\Doctrine\RegistryInterface;

use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\CurrencyBundle\Provider\RepositoryCurrencyCheckerProviderInterface;

class CurrencyCheckerProvider implements RepositoryCurrencyCheckerProviderInterface
{
    const ENTITY_LABEL = 'oro.sales.opportunity.entity_label';

    /**
     * @var RegistryInterface
     */
    protected $doctrine;

    public function __construct(RegistryInterface $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @inheritDoc
     */
    public function getEntityLabel()
    {
        return self::ENTITY_LABEL;
    }

    /**
     * @inheritdoc
     */
    public function hasRecordsInCurrenciesOnRemove(
        array $currenciesOnRemove,
        Organization $organization = null
    ) {
        $opportunityRepository = $this->doctrine->getRepository('OroSalesBundle:Opportunity');
        return $opportunityRepository->hasRecordsInCurrenciesOnRemove($currenciesOnRemove, $organization);
    }
}
