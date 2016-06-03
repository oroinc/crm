<?php

namespace OroCRM\Bundle\SalesBundle\Command;

use Doctrine\ORM\EntityManager;

use OroCRM\Bundle\ChannelBundle\Command\RecalculateLifetimeCommand as AbstractRecalculateLifetimeCommand;
use OroCRM\Bundle\SalesBundle\Entity\B2bCustomer;
use OroCRM\Bundle\SalesBundle\Entity\Repository\B2bCustomerRepository;

class RecalculateLifetimeCommand extends AbstractRecalculateLifetimeCommand
{
    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        parent::configure();

        $this
            ->setName('oro:b2b:lifetime:recalculate')
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
        $customerRepo  = $em->getRepository('OroCRMSalesBundle:B2bCustomer');
        $lifetimeValue = $customerRepo->calculateLifetimeValue($customer);

        return $lifetimeValue;
    }
}
