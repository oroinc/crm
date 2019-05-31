<?php

namespace Oro\Bundle\MagentoBundle\Command;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\ChannelBundle\Command\RecalculateLifetimeCommand as AbstractRecalculateLifetimeCommand;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Entity\Repository\CustomerRepository;
use Oro\Bundle\MagentoBundle\Provider\MagentoChannelType;

/**
 * Performs re-calculation of lifetime values for Magento channel.
 */
class RecalculateLifetimeCommand extends AbstractRecalculateLifetimeCommand
{
    /** @var string */
    protected static $defaultName = 'oro:magento:lifetime:recalculate';

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        parent::configure();

        $this
            ->setDescription('Perform re-calculation of lifetime values for Magento channel.');
    }

    /**
     * {@inheritdoc}
     */
    protected function getChannelType()
    {
        return MagentoChannelType::TYPE;
    }

    /**
     * @param EntityManager $em
     * @param Customer      $customer
     *
     * @return float
     */
    protected function calculateCustomerLifetime(EntityManager $em, $customer)
    {
        /** @var CustomerRepository $customerRepo */
        $customerRepo  = $em->getRepository('OroMagentoBundle:Customer');
        $lifetimeValue = $customerRepo->calculateLifetimeValue($customer);

        return $lifetimeValue;
    }
}
