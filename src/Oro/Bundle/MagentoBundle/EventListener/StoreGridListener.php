<?php

namespace Oro\Bundle\MagentoBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\MagentoBundle\Entity\MagentoTransport;
use Oro\Bundle\MagentoBundle\Entity\Website;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Listener for grid "magento-store-by-channel-grid".
 *
 * @see \Oro\Bundle\MagentoBundle\Autocomplete\StoreSearchHandler
 */
class StoreGridListener
{
    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /** @var string */
    protected $dataChannelClass;

    /** @var EntityManager */
    protected $entityManager;

    /**
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param string                        $dataChannelClass
     * @param EntityManager                 $entityManager
     */
    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        $dataChannelClass,
        EntityManager $entityManager
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->dataChannelClass = $dataChannelClass;
        $this->entityManager = $entityManager;
    }

    /**
     * Limit stores to website selected in integration settings
     * if ASSIGN permission for integration channel is granted
     *
     * @param BuildAfter $event
     */
    public function onBuildAfter(BuildAfter $event)
    {
        $datagrid   = $event->getDatagrid();
        $datasource = $datagrid->getDatasource();
        if ($datasource instanceof OrmDatasource) {
            if ($this->authorizationChecker->isGranted('oro_integration_assign')) {
                $parameters = $datagrid->getParameters();
                $channelIds = $parameters->get('channelIds');
                $dataChannel = $this->getDataChannelById($channelIds);
                if ($dataChannel) {
                    $transport = $dataChannel->getDataSource()->getTransport();
                    if ($transport instanceof MagentoTransport) {
                        $websiteId = $transport->getSettingsBag()->get('website_id');
                        if ($websiteId !== Website::ALL_WEBSITES) {
                            $datasource
                                ->getQueryBuilder()
                                ->andWhere('w.originId = :id')
                                ->setParameter('id', $websiteId);
                        }
                    }
                }
            } else {
                // if permission for integration channel assign is not granted
                $datasource->getQueryBuilder()
                    ->andWhere('1 = 0');
            }
        }
    }

    /**
     * @param int $dataChannelId
     * @return Channel
     */
    protected function getDataChannelById($dataChannelId)
    {
        return $this->entityManager->find($this->dataChannelClass, $dataChannelId);
    }
}
