<?php

namespace Oro\Bundle\SalesBundle\Provider\Opportunity;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Translation\TranslatorInterface;

use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\CurrencyBundle\Provider\RepositoryCurrencyProviderInterface;

class CurrencyListProvider implements RepositoryCurrencyProviderInterface
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
