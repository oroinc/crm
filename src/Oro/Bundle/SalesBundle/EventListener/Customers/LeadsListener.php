<?php

namespace Oro\Bundle\SalesBundle\EventListener\Customers;

use Symfony\Component\Translation\TranslatorInterface;

use Oro\Bundle\SalesBundle\Entity\B2bCustomer;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\UIBundle\Event\BeforeViewRenderEvent;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureChecker;
use Oro\Bundle\SalesBundle\Provider\Customer\ConfigProvider as CustomerConfigProvider;

class LeadsListener
{
    // below opportunity block which have 1010
    const GRID_BLOCK_PRIORITY = 1020;

    /** @var CustomerConfigProvider */
    protected $customerConfigProvider;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /** @var  FeatureChecker */
    protected $featureChecker;

    /**
     * @param CustomerConfigProvider $customerConfigProvider
     * @param TranslatorInterface $translator
     * @param DoctrineHelper $helper
     * @param FeatureChecker $featureChecker
     */
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
     *
     * @param BeforeViewRenderEvent $event
     */
    public function addLeads(BeforeViewRenderEvent $event)
    {
        if (!$this->featureChecker->isFeatureEnabled('sales_lead')) {
            return;
        }

        $entity = $event->getEntity();
        if ($this->customerConfigProvider->isCustomerClass($entity) && $entity instanceof B2bCustomer) {
            $environment          = $event->getTwigEnvironment();
            $data                 = $event->getData();
            $leadsData    = $environment->render(
                'OroSalesBundle:Customer:leadsGrid.html.twig',
                ['gridParams' =>
                    [
                        'business_customer_id'    => $this->doctrineHelper->getSingleEntityIdentifier($entity)
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
