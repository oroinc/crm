<?php

namespace Oro\Bundle\SalesBundle\EventListener\Customers;

use Oro\Bundle\SalesBundle\Provider\Customer\OpportunitiesGrid\BlockPriorityProviderInterface;
use Symfony\Component\Translation\TranslatorInterface;

use Doctrine\Common\Util\ClassUtils;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\SalesBundle\Provider\Customer\ConfigProvider;
use Oro\Bundle\UIBundle\Event\BeforeViewRenderEvent;

class OpportunitiesListener
{
    /** @var ConfigProvider */
    protected $customerConfigProvider;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /** @var BlockPriorityProviderInterface */
    protected $priorityProvider;

    /**
     * @param ConfigProvider                 $customerConfigProvider
     * @param TranslatorInterface            $translator
     * @param DoctrineHelper                 $helper
     * @param BlockPriorityProviderInterface $priorityProvider
     */
    public function __construct(
        ConfigProvider $customerConfigProvider,
        TranslatorInterface $translator,
        DoctrineHelper $helper,
        BlockPriorityProviderInterface $priorityProvider
    ) {
        $this->customerConfigProvider = $customerConfigProvider;
        $this->translator             = $translator;
        $this->doctrineHelper         = $helper;
        $this->priorityProvider       = $priorityProvider;
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
            $targetClass          = ClassUtils::getClass($entity);
            $priority             = $this->priorityProvider->getPriority($targetClass);
            $opportunitiesData    = $environment->render(
                'OroSalesBundle:Customer:opportunitiesGrid.html.twig',
                ['gridParams' =>
                     [
                         'customer_id'    => $this->doctrineHelper->getSingleEntityIdentifier($entity),
                         'customer_class' => $targetClass,
                     ]
                ]
            );
            $data['dataBlocks'][] = [
                'title'     => $this->translator->trans('oro.sales.customers.opportunities.grid.label'),
                'priority'  => (int)$priority,
                'subblocks' => [['data' => [$opportunitiesData]]]
            ];
            $event->setData($data);
        }
    }
}
