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

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    public function __construct(RegistryInterface $doctrine, TranslatorInterface $translator)
    {
        $this->doctrine = $doctrine;
        $this->translator = $translator;
    }

    /**
     * @inheritDoc
     */
    public function getEntityLabel()
    {
        return $this->translator->trans(self::ENTITY_LABEL);
    }

    /**
     * @inheritdoc
     */
    public function hasRecordsInUnavailableCurrencies(
        array $availableCurrencies,
        Organization $organization = null
    ) {
        $opportunityRepository = $this->doctrine->getRepository('OroSalesBundle:Opportunity');
        return $opportunityRepository->hasRecordsInUnavailableCurrencies($availableCurrencies, $organization);
    }
}
