<?php

namespace Oro\Bundle\SalesBundle\EventListener\Customers;

use Doctrine\Common\Util\ClassUtils;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureChecker;
use Oro\Bundle\SalesBundle\Entity\B2bCustomer;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Provider\Customer\ConfigProvider as CustomerConfigProvider;
use Oro\Bundle\UIBundle\Event\BeforeViewRenderEvent;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Adds block with leads grid on the B2bCustomer view
 */
class LeadsListener
{
    // above opportunity block which have 1010
    const GRID_BLOCK_PRIORITY = 1005;

    /** @var CustomerConfigProvider */
    protected $customerConfigProvider;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /** @var  FeatureChecker */
    protected $featureChecker;

    public function __construct(
        CustomerConfigProvider $customerConfigProvider,
        TranslatorInterface $translator,
        DoctrineHelper $helper,
        FeatureChecker $featureChecker
    ) {
        $this->customerConfigProvider = $customerConfigProvider;
        $this->translator             = $translator;
        $this->doctrineHelper         = $helper;
        $this->featureChecker         = $featureChecker;
    }

    /**
     * Adds block with leads grid on the B2bCustomer view
     */
    public function addLeads(BeforeViewRenderEvent $event)
    {
        if (!$this->featureChecker->isFeatureEnabled('sales_lead')) {
            return;
        }

        $entity = $event->getEntity();
        if ($entity && ClassUtils::getClass($entity) === B2bCustomer::class) {
            $environment  = $event->getTwigEnvironment();
            $data         = $event->getData();
            $targetClass  = ClassUtils::getClass($entity);
            $leadsData    = $environment->render(
                '@OroSales/Customer/leadsGrid.html.twig',
                [
                    'gridParams' =>
                        [
                            'customer_id'    => $this->doctrineHelper->getSingleEntityIdentifier($entity),
                            'customer_class' => $targetClass,
                            'related_entity_class' => Lead::class,
                        ]
                ]
            );
            $data['dataBlocks'][] = [
                'title'     => $this->translator->trans('oro.sales.customers.leads.grid.label'),
                'priority'  => self::GRID_BLOCK_PRIORITY,
                'subblocks' => [['data' => [$leadsData]]]
            ];
            $event->setData($data);
        }
    }
}
