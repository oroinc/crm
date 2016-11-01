<?php

namespace Oro\Bundle\SalesBundle\EventListener\Customers;

use Symfony\Component\Translation\TranslatorInterface;

use Doctrine\Common\Util\ClassUtils;

use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\UIBundle\Event\BeforeViewRenderEvent;
use Oro\Bundle\SalesBundle\Provider\Customers\OpportunitiesProvider;

class OpportunitiesListener
{
    /** @var ConfigProvider */
    protected $extendProvider;

    /** @var OpportunitiesProvider */
    protected $provider;

    /** @var TranslatorInterface */
    protected $translator;

    /**
     * @param ConfigManager         $configManager
     * @param OpportunitiesProvider $provider
     * @param TranslatorInterface   $translator
     */
    public function __construct(
        ConfigManager $configManager,
        OpportunitiesProvider $provider,
        TranslatorInterface $translator
    ) {
        $this->extendProvider = $configManager->getProvider('extend');
        $this->provider       = $provider;
        $this->translator     = $translator;
    }

    /**
     * @param BeforeViewRenderEvent $event
     */
    public function addSalesItems(BeforeViewRenderEvent $event)
    {
        $entity      = $event->getEntity();
        $entityClass = ClassUtils::getClass($entity);
        if ($entity && $this->provider->supportCustomer($entityClass)) {
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
