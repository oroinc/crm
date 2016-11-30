<?php

namespace Oro\Bundle\SalesBundle\EventListener\Customers;

use Symfony\Component\Translation\TranslatorInterface;

use Doctrine\Common\Util\ClassUtils;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\SalesBundle\Provider\Customer\ConfigProvider;
use Oro\Bundle\UIBundle\Event\BeforeViewRenderEvent;

class OpportunitiesListener
{
    // below activity block which have 1000
    const GRID_BLOCK_PRIORITY = 1010;

    /** @var ConfigProvider */
    protected $customerConfigProvider;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /**
     * @param ConfigProvider $customerConfigProvider
     * @param TranslatorInterface $translator
     * @param DoctrineHelper $helper
     */
    public function __construct(
        ConfigProvider $customerConfigProvider,
        TranslatorInterface $translator,
        DoctrineHelper $helper
    ) {
        $this->customerConfigProvider           = $customerConfigProvider;
        $this->translator                       = $translator;
        $this->doctrineHelper                   = $helper;
    }

    /**
     * Adds block with associated opportunities grid of viewing entity
     * if this entity has "customer" association enabled.
     *
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
                         'customer_id'    => $this->doctrineHelper->getSingleEntityIdentifier($entity),
                         'customer_class' => ClassUtils::getClass($entity)
                     ]
                ]
            );
            $data['dataBlocks'][] = [
                'title'     => $this->translator->trans('oro.sales.customers.opportunities.grid.label'),
                'priority' => self::GRID_BLOCK_PRIORITY,
                'subblocks' => [['data' => [$opportunitiesData]]]
            ];
            $event->setData($data);
        }
    }
}
