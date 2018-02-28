<?php

namespace Oro\Bundle\MagentoBundle\Manager;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\WorkflowBundle\Entity\EventTriggerInterface;
use Oro\Bundle\WorkflowBundle\EventListener\Extension\ProcessTriggerExtension;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class CustomerContactManager implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var EntityManager */
    protected $em;

    /** @var ProcessTriggerExtension  */
    protected $triggerExtension;

    /**
     * @param EntityManager $em
     * @param ProcessTriggerExtension $triggerExtension
     */
    public function __construct(EntityManager $em, ProcessTriggerExtension $triggerExtension)
    {
        $this->em = $em;
        $this->triggerExtension = $triggerExtension;
    }

    /**
     * @param int[]|null $integrationIds
     * @param int $batchSize
     */
    public function fillContacts($integrationIds = null, $batchSize = 25)
    {
        $i = 0;
        $this->logger->info(sprintf('Start process'));
        $repository = $this->em->getRepository('OroMagentoBundle:Customer');

        $iterator = $repository->getIteratorByIdsAndIntegrationIds(null, $integrationIds);
        $iterator->setBufferSize($batchSize);
        $customerCount = $iterator->count();

        $iterator->setPageCallback(function () use (&$i, $customerCount) {
            $this->triggerExtension->process($this->em);
            $this->logger->info(sprintf('Processed %s customers from %s', $i, $customerCount));
        });

        /** @var Customer $customer */
        foreach ($iterator as $customer) {
            $i++;
            $contact = $customer->getContact();
            if (!$contact) {
                $this->triggerExtension->schedule($customer, EventTriggerInterface::EVENT_CREATE);
            }
        }

        $this->logger->info(sprintf('Finish process'));
    }
}
