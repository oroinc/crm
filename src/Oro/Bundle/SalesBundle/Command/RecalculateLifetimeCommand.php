<?php

namespace Oro\Bundle\SalesBundle\Command;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Oro\Bundle\ChannelBundle\Command\RecalculateLifetimeCommand as AbstractRecalculateLifetimeCommand;
use Oro\Bundle\ChannelBundle\Provider\SettingsProvider;
use Oro\Bundle\CurrencyBundle\Query\CurrencyQueryBuilderTransformerInterface;
use Oro\Bundle\SalesBundle\Entity\B2bCustomer;
use Oro\Bundle\SalesBundle\Entity\Repository\B2bCustomerRepository;

/**
 * Perform re-calculation of lifetime values for sales channel.
 */
class RecalculateLifetimeCommand extends AbstractRecalculateLifetimeCommand
{
    /** @var string */
    protected static $defaultName = 'oro:b2b:lifetime:recalculate';

    /** @var CurrencyQueryBuilderTransformerInterface */
    private $currencyTransformer;

    /**
     * @param ManagerRegistry $registry
     * @param SettingsProvider $settingsProvider
     * @param CurrencyQueryBuilderTransformerInterface $currencyTransformer
     */
    public function __construct(
        ManagerRegistry $registry,
        SettingsProvider $settingsProvider,
        CurrencyQueryBuilderTransformerInterface $currencyTransformer
    ) {
        parent::__construct($registry, $settingsProvider);

        $this->currencyTransformer = $currencyTransformer;
    }

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        parent::configure();

        $this
            ->setDescription('Perform re-calculation of lifetime values for sales channel.');
    }

    /**
     * {@inheritdoc}
     */
    protected function getChannelType()
    {
        return 'b2b';
    }

    /**
     * @param EntityManager $em
     * @param B2bCustomer   $customer
     *
     * @return float
     */
    protected function calculateCustomerLifetime(EntityManager $em, $customer)
    {
        /** @var B2bCustomerRepository $customerRepo */
        $customerRepo  = $em->getRepository('OroSalesBundle:B2bCustomer');
        $qbTransformer = $this->currencyTransformer;
        $lifetimeValue = $customerRepo->calculateLifetimeValue($customer, $qbTransformer);

        return $lifetimeValue;
    }
}
