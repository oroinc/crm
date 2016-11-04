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
    protected $salesProvider;

    /** @var TranslatorInterface */
    protected $translator;

    /**
     * @param ConfigManager         $configManager
     * @param TranslatorInterface   $translator
     */
    public function __construct(ConfigManager $configManager, TranslatorInterface $translator )
    {
        $this->extendProvider = $configManager->getProvider('sales');
        $this->translator     = $translator;
    }

    /**
     * @param BeforeViewRenderEvent $event
     */
    public function addOpportunities(BeforeViewRenderEvent $event)
    {
        $entity      = $event->getEntity();
        $class2      = ClassUtils::getClass(null);
        $class1      = ClassUtils::getClass(false);
        $class       = ClassUtils::getClass('');
        $entityClass = ClassUtils::getClass($entity);
        if ($entity && !empty($this->salesProvider->getConfig($entityClass)['opportunity'])) {
            $environment          = $event->getTwigEnvironment();
            $data                 = $event->getData();
            $opportunitiesData    = $environment->render(
                'OroSalesBundle:Customers:opportunitiesGrid.html.twig',
                ['customer' => $entity, 'customerClass' => $entityClass]
            );
            $data['dataBlocks'][] = [
                'title'     => $this->translator->trans('oro.sales.customers.opportunities.grid.label'),
                'subblocks' => [['data' => [$opportunitiesData]]],
                'priority'  => 10000
            ];
            $event->setData($data);
        }
    }
}
