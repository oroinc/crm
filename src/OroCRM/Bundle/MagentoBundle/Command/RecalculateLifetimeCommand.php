<?php

namespace OroCRM\Bundle\MagentoBundle\Command;

use Doctrine\ORM\EntityManager;

use OroCRM\Bundle\ChannelBundle\Command\RecalculateLifetimeCommand as AbstractRecalculateLifetimeCommand;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Entity\Repository\CustomerRepository;
use OroCRM\Bundle\MagentoBundle\Provider\ChannelType;

class RecalculateLifetimeCommand extends AbstractRecalculateLifetimeCommand
{
    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        parent::configure();

        $this
            ->setName('oro:magento:lifetime:recalculate')
            ->setDescription('Perform re-calculation of lifetime values for Magento channel.');
    }

    /**
     * {@inheritdoc}
     */
    protected function getChannelType()
    {
        return ChannelType::TYPE;
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
        $customerRepo  = $em->getRepository('OroCRMMagentoBundle:Customer');
        $lifetimeValue = $customerRepo->calculateLifetimeValue($customer);

        return $lifetimeValue;
    }
}
