<?php

declare(strict_types=1);

namespace Oro\Bundle\SalesBundle\Command;

use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ChannelBundle\Command\RecalculateLifetimeCommand as AbstractRecalculateLifetimeCommand;
use Oro\Bundle\ChannelBundle\Provider\SettingsProvider;
use Oro\Bundle\CurrencyBundle\Query\CurrencyQueryBuilderTransformerInterface;
use Oro\Bundle\SalesBundle\Entity\B2bCustomer;
use Oro\Bundle\SalesBundle\Entity\Repository\B2bCustomerRepository;
use Symfony\Component\Console\Attribute\AsCommand;

/**
 * Recalculates lifetime values for (offline) B2B sales channel customers.
 */
#[AsCommand(
    name: 'oro:b2b:lifetime:recalculate',
    description: 'Recalculates lifetime values for (offline) B2B sales channel customers.'
)]
class RecalculateLifetimeCommand extends AbstractRecalculateLifetimeCommand
{
    private CurrencyQueryBuilderTransformerInterface $currencyTransformer;

    public function __construct(
        ManagerRegistry $registry,
        SettingsProvider $settingsProvider,
        CurrencyQueryBuilderTransformerInterface $currencyTransformer
    ) {
        parent::__construct($registry, $settingsProvider);

        $this->currencyTransformer = $currencyTransformer;
    }

    #[\Override]
    public function configure()
    {
        parent::configure();

        $this
            ->addUsage('--force');
    }

    #[\Override]
    protected function getChannelType(): string
    {
        return 'b2b';
    }

    /**
     * @param EntityManager $em
     * @param B2bCustomer $customer
     * @return float
     */
    #[\Override]
    protected function calculateCustomerLifetime(EntityManager $em, object $customer): float
    {
        /** @var B2bCustomerRepository $customerRepo */
        $customerRepo  = $em->getRepository(B2bCustomer::class);

        return $customerRepo->calculateLifetimeValue($customer, $this->currencyTransformer);
    }
}
