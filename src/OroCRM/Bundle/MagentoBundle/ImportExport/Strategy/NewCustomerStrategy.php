<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Strategy;

use OroCRM\Bundle\ChannelBundle\ImportExport\Helper\ChannelHelper;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;

class NewCustomerStrategy extends AbstractImportStrategy
{
    /**
     * @var ChannelHelper
     */
    protected $channelHelper;

    /**
     * @param ChannelHelper $channelHelper
     */
    public function setChannelHelper(ChannelHelper $channelHelper)
    {
        $this->channelHelper = $channelHelper;
    }

    /**
     * @param Customer $entity
     * @return Customer
     */
    protected function afterProcessEntity($entity)
    {
        if ($entity->getWebsite()) {
            $this->updateRelations($entity->getWebsite());
        }

        if ($entity->getStore()) {
            $this->updateRelations($entity->getStore());
            $entity->getStore()->setWebsite($entity->getWebsite());
        }

        if ($entity->getChannel()) {
            $dataChannel = $this->channelHelper->getChannel($entity->getChannel());
            if ($dataChannel) {
                $entity->setDataChannel($dataChannel);
            }
        }

        if ($entity->getGroup()) {
            $this->updateRelations($entity->getGroup());
        }

        if (!$entity->getAddresses()->isEmpty()) {
            foreach ($entity->getAddresses() as &$address) {
                $this->updateRelations($address);
            }
        }

        return parent::afterProcessEntity($entity);
    }
}
