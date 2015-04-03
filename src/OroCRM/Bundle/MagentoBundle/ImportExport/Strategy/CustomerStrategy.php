<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Strategy;

use OroCRM\Bundle\ChannelBundle\ImportExport\Helper\ChannelHelper;
use OroCRM\Bundle\MagentoBundle\Entity\Address;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\ImportExport\Strategy\StrategyHelper\AddressImportHelper;
use OroCRM\Bundle\MagentoBundle\ImportExport\Strategy\StrategyHelper\CustomerGroupHelper;

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
     * @var CustomerGroupHelper
     */
    protected $customerGroupHelper;

    /**
     * @var Address[]
     */
    protected $importingAddresses = [];

    /**
     * @var array
     */
    protected $addressRegions = [];

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
     * @param CustomerGroupHelper $customerGroupHelper
     * @return CustomerStrategy
     */
    public function setCustomerGroupHelper($customerGroupHelper)
    {
        $this->customerGroupHelper = $customerGroupHelper;

        return $this;
    }

    /**
     * @param Customer $entity
     * @return Customer
     */
    protected function beforeProcessEntity($entity)
    {
        $this->importingAddresses = [];
        $this->addressRegions = [];
        $importingAddresses = $entity->getAddresses();
        if ($importingAddresses) {
            foreach ($importingAddresses as $address) {
                $originId = $address->getOriginId();
                $this->importingAddresses[$originId] = $address;

                if ($address->getRegion()) {
                    $this->addressRegions[$originId] = $address->getRegion()->getCombinedCode();
                } else {
                    $this->addressRegions[$originId] = null;
                }
            }
        }

        if ($entity->getGroup()) {
            $entity->getGroup()->setChannel($entity->getChannel());
        }

        return parent::beforeProcessEntity($entity);
    }

    /**
     * @param Customer $entity
     * @return Customer
     */
    protected function afterProcessEntity($entity)
    {
        $this->processDataChannel($entity);
        $this->processGroup($entity);
        $this->processAddresses($entity);

        return parent::afterProcessEntity($entity);
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
        $group = $entity->getGroup();
        if ($group) {
            $group = $this->customerGroupHelper->getUniqueGroup($group);
            $group->setChannel($entity->getChannel());
            $entity->setGroup($group);
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

                    if (!empty($this->addressRegions[$originId]) && $address->getCountry()) {
                        $this->addressHelper->updateRegionByMagentoRegionId(
                            $address,
                            $address->getCountry()->getIso2Code(),
                            $this->addressRegions[$originId]
                        );
                    }
                }
            }
        }
    }
}
