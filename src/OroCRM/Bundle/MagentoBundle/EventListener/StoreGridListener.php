<?php

namespace OroCRM\Bundle\MagentoBundle\EventListener;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\SecurityBundle\SecurityFacade;

use OroCRM\Bundle\MagentoBundle\Entity\MagentoSoapTransport;
use OroCRM\Bundle\MagentoBundle\Provider\Iterator\StoresSoapIterator;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;

/**
 * Listener for grid "magento-store-by-channel-grid".
 *
 * @see \OroCRM\Bundle\MagentoBundle\Autocomplete\StoreSearchHandler
 */
class StoreGridListener
{
    /** @var SecurityFacade */
    protected $securityFacade;

    /** @var string */
    protected $dataChannelClass;

    /** @var EntityManager */
    protected $entityManager;

    /**
     * @param SecurityFacade $securityFacade
     * @param string $dataChannelClass
     * @param EntityManager $entityManager
     */
    public function __construct(SecurityFacade $securityFacade, $dataChannelClass, EntityManager $entityManager)
    {
        $this->securityFacade   = $securityFacade;
        $this->dataChannelClass = $dataChannelClass;
        $this->entityManager    = $entityManager;
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
            $parameters = $datagrid->getParameters();

            if ($this->securityFacade->isGranted('oro_integration_assign')) {
                $channelIds = $parameters->get('channelIds');
                $dataChannel = $this->getDataChannelById($channelIds);
                if ($dataChannel) {
                    $transport = $dataChannel->getDataSource()->getTransport();
                    if ($transport instanceof MagentoSoapTransport) {
                        $websiteId = $transport->getSettingsBag()->get('website_id');
                        if ($websiteId !== StoresSoapIterator::ALL_WEBSITES) {
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
        /** @var Channel $result */
        $result = $this->entityManager->find($this->dataChannelClass, $dataChannelId);
        return $result;
    }
}
