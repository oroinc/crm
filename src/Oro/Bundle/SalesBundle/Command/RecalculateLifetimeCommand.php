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

/**
 * Recalculates lifetime values for (offline) B2B sales channel customers.
 */
class RecalculateLifetimeCommand extends AbstractRecalculateLifetimeCommand
{
    /** @var string */
    protected static $defaultName = 'oro:b2b:lifetime:recalculate';

    private CurrencyQueryBuilderTransformerInterface $currencyTransformer;

    public function __construct(
        ManagerRegistry $registry,
        SettingsProvider $settingsProvider,
        CurrencyQueryBuilderTransformerInterface $currencyTransformer
    ) {
        parent::__construct($registry, $settingsProvider);

        $this->currencyTransformer = $currencyTransformer;
    }

    public function configure()
    {
        parent::configure();

        $this->setDescription('Recalculates lifetime values for (offline) B2B sales channel customers.')
            ->addUsage('--force');
    }

    protected function getChannelType(): string
    {
        return 'b2b';
    }

    /**
     * @param B2bCustomer $customer
     */
    protected function calculateCustomerLifetime(EntityManager $em, object $customer): float
    {
        /** @var B2bCustomerRepository $customerRepo */
        $customerRepo  = $em->getRepository('OroSalesBundle:B2bCustomer');

        return $customerRepo->calculateLifetimeValue($customer, $this->currencyTransformer);
    }
}
