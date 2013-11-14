<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\IntegrationBundle\Provider\SyncProcessorInterface;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;

class CustomerSyncProcessor implements SyncProcessorInterface
{
    /** @var EntityManager */
    protected $em;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param $batchData
     * @return bool
     */
    public function process($batchData)
    {
        foreach ($batchData as $item) {
            $this->em->persist($this->createCustomer($item));
        }

        $this->em->flush();

        return true;
    }

    /**
     * @param $customerData
     *
     * @return Customer
     */
    protected function createCustomer($customerData)
    {
        $customer = new Customer();
        $customer->fillFromArray($customerData);

        return $customer;
    }

}
