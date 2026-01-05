<?php

namespace Oro\Bundle\SalesBundle\Provider\Opportunity;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CurrencyBundle\Provider\RepositoryCurrencyCheckerProviderInterface;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SalesBundle\Entity\Opportunity;

/**
 * Provides information about opportunity entity for currency checkers.
 */
class CurrencyCheckerProvider implements RepositoryCurrencyCheckerProviderInterface
{
    public const ENTITY_LABEL = 'oro.sales.opportunity.entity_label';

    /**
     * @var ManagerRegistry
     */
    protected $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    #[\Override]
    public function getEntityLabel()
    {
        return self::ENTITY_LABEL;
    }

    #[\Override]
    public function hasRecordsWithRemovingCurrencies(
        array $removingCurrencies,
        ?Organization $organization = null
    ) {
        $opportunityRepository = $this->doctrine->getRepository(Opportunity::class);
        return $opportunityRepository->hasRecordsWithRemovingCurrencies($removingCurrencies, $organization);
    }
}
