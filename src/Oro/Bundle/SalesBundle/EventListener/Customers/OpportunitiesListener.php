<?php

namespace Oro\Bundle\SalesBundle\EventListener\Customers;

use Doctrine\Common\Util\ClassUtils;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\SalesBundle\Provider\Customer\CustomerConfigProvider;
use Oro\Bundle\UIBundle\Event\BeforeViewRenderEvent;
use Symfony\Component\Translation\TranslatorInterface;

class OpportunitiesListener
{
    /** @var CustomerConfigProvider */
    protected $customerConfigProvider;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /**
     * @param CustomerConfigProvider $customerConfigProvider
     * @param TranslatorInterface $translator
     * @param DoctrineHelper      $helper
     */
    public function __construct(
        CustomerConfigProvider $customerConfigProvider,
        TranslatorInterface $translator,
        DoctrineHelper $helper
    ) {
        $this->customerConfigProvider = $customerConfigProvider;
        $this->translator          = $translator;
        $this->doctrineHelper      = $helper;
    }

    /**
     * @param BeforeViewRenderEvent $event
     */
    public function addOpportunities(BeforeViewRenderEvent $event)
    {
        $entity = $event->getEntity();
        if ($this->customerConfigProvider->isCustomerClass($entity)) {
            $environment          = $event->getTwigEnvironment();
            $data                 = $event->getData();
            $opportunitiesData    = $environment->render(
                'OroSalesBundle:Customer:opportunitiesGrid.html.twig',
                ['gridParams' =>
                     [
                         'customer_id' => $this->doctrineHelper->getSingleEntityIdentifier($entity),
                         'customer_class' => ClassUtils::getClass($entity),
                     ]
                ]
            );
            $data['dataBlocks'][] = [
                'title'     => $this->translator->trans('oro.sales.customers.opportunities.grid.label'),
                'subblocks' => [['data' => [$opportunitiesData]]]
            ];
            $event->setData($data);
        }
    }
}
