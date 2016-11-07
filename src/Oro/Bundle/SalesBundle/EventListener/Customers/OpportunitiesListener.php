<?php

namespace Oro\Bundle\SalesBundle\EventListener\Customers;

use Symfony\Component\Translation\TranslatorInterface;

use Doctrine\Common\Util\ClassUtils;

use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\UIBundle\Event\BeforeViewRenderEvent;

class OpportunitiesListener
{
    /** @var ConfigProvider */
    protected $opportunityProvider;

    /** @var TranslatorInterface */
    protected $translator;

    /**
     * @param ConfigManager       $configManager
     * @param TranslatorInterface $translator
     */
    public function __construct(ConfigManager $configManager, TranslatorInterface $translator)
    {
        $this->opportunityProvider = $configManager->getProvider('opportunity');
        $this->translator          = $translator;
    }

    /**
     * @param BeforeViewRenderEvent $event
     */
    public function addOpportunities(BeforeViewRenderEvent $event)
    {
        $entity       = $event->getEntity();
        $entityClass  = ClassUtils::getClass($entity);
        $entityConfig = $this->opportunityProvider->hasConfig($entityClass)
            ? $this->opportunityProvider->getConfig($entityClass)
            : null;
        if ($entity && $entityConfig && $entityConfig->is('enabled')) {
            $environment          = $event->getTwigEnvironment();
            $data                 = $event->getData();
            $opportunitiesData    = $environment->render(
                'OroSalesBundle:Customers:opportunitiesGrid.html.twig',
                ['customer' => $entity, 'customerClass' => $entityClass]
            );
            $data['dataBlocks'][] = [
                'title'     => $this->translator->trans('oro.sales.customers.opportunities.grid.label'),
                'subblocks' => [['data' => [$opportunitiesData]]]
            ];
            $event->setData($data);
        }
    }
}
