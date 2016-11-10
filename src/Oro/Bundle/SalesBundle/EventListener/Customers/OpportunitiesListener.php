<?php

namespace Oro\Bundle\SalesBundle\EventListener\Customers;

use Symfony\Component\Translation\TranslatorInterface;

use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\UIBundle\Event\BeforeViewRenderEvent;

class OpportunitiesListener
{
    /** @var ConfigProvider */
    protected $opportunityProvider;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /**
     * @param ConfigManager       $configManager
     * @param TranslatorInterface $translator
     * @param DoctrineHelper      $helper
     */
    public function __construct(ConfigManager $configManager, TranslatorInterface $translator, DoctrineHelper $helper)
    {
        $this->opportunityProvider = $configManager->getProvider('opportunity');
        $this->translator          = $translator;
        $this->doctrineHelper      = $helper;
    }

    /**
     * @param BeforeViewRenderEvent $event
     */
    public function addOpportunities(BeforeViewRenderEvent $event)
    {
        $entity       = $event->getEntity();
        $entityConfig = $entity && $this->opportunityProvider->hasConfig($entity)
            ? $this->opportunityProvider->getConfig($entity)
            : null;
        if ($entityConfig && $entityConfig->is('enabled')) {
            $environment          = $event->getTwigEnvironment();
            $data                 = $event->getData();
            $opportunitiesData    = $environment->render(
                'OroSalesBundle:Customers:opportunitiesGrid.html.twig',
                ['gridParams' =>
                     [
                         'customer_id' => $this->doctrineHelper->getSingleEntityIdentifier($entity),
                         'customer_class' => $entityConfig->getId()->getClassName()
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
