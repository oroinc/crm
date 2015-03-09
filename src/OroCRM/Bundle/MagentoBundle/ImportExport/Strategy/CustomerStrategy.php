<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Strategy;

use OroCRM\Bundle\ChannelBundle\ImportExport\Helper\ChannelHelper;
use OroCRM\Bundle\MagentoBundle\Entity\Address;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\ImportExport\Strategy\StrategyHelper\AddressImportHelper;

class CustomerStrategy extends AbstractImportStrategy
{
    /**
     * @var ChannelHelper
     */
    protected $channelHelper;

    /**
     * @var AddressImportHelper
     */
    protected $addressHelper;

    /**
     * @var StoreStrategy
     */
    protected $storeStrategy;

    /**
     * @var Address[]
     */
    protected $importingAddresses;

    /**
     * @param ChannelHelper $channelHelper
     */
    public function setChannelHelper(ChannelHelper $channelHelper)
    {
        $this->channelHelper = $channelHelper;
    }

    /**
     * @param AddressImportHelper $addressHelper
     */
    public function setAddressHelper(AddressImportHelper $addressHelper)
    {
        $this->addressHelper = $addressHelper;
    }

    /**
     * @param StoreStrategy $storeStrategy
     */
    public function setStoreStrategy(StoreStrategy $storeStrategy)
    {
        $this->storeStrategy = $storeStrategy;
    }

    /**
     * @param Customer $entity
     * @return Customer
     */
    protected function beforeProcessEntity($entity)
    {
        $importingAddresses = $entity->getAddresses();
        if ($importingAddresses) {
            foreach ($importingAddresses as $address) {
                $this->importingAddresses[$address->getOriginId()] = $address;
            }
        }

        return parent::beforeProcessEntity($entity);
    }

    /**
     * @param Customer $entity
     * @return Customer
     */
    protected function afterProcessEntity($entity)
    {
        $this->processStore($entity);
        $this->processDataChannel($entity);
        $this->processGroup($entity);
        $this->processAddresses($entity);

        return parent::afterProcessEntity($entity);
    }

    /**
     * @param Customer $entity
     */
    protected function processStore(Customer $entity)
    {
        $store = $entity->getStore();
        if ($entity->getStore()) {
            $store = $this->storeStrategy->process($store);

            $entity->setStore($store);
            $entity->setWebsite($store->getWebsite());
        }
    }

    /**
     * @param Customer $entity
     */
    protected function processDataChannel(Customer $entity)
    {
        if ($entity->getChannel()) {
            $dataChannel = $this->channelHelper->getChannel($entity->getChannel());
            if ($dataChannel) {
                $entity->setDataChannel($dataChannel);
            }
        }
    }

    /**
     * @param Customer $entity
     */
    protected function processGroup(Customer $entity)
    {
        if ($entity->getGroup()) {
            $this->updateRelations($entity->getGroup());
        }
    }

    /**
     * @param Customer $entity
     */
    protected function processAddresses(Customer $entity)
    {
        if (!$entity->getAddresses()->isEmpty()) {
            foreach ($entity->getAddresses() as $address) {
                $originId = $address->getOriginId();
                if (array_key_exists($originId, $this->importingAddresses)) {
                    $remoteAddress = $this->importingAddresses[$originId];
                    $this->addressHelper->mergeAddressTypes($address, $remoteAddress);
                }
            }
        }
    }
}
