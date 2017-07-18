<?php

namespace Oro\Bundle\MagentoBundle\ImportExport\Processor;

use Oro\Bundle\ImportExportBundle\Exception\InvalidArgumentException;
use Oro\Bundle\IntegrationBundle\Exception\TransportException;
use Oro\Bundle\IntegrationBundle\ImportExport\Processor\StepExecutionAwareExportProcessor;
use Oro\Bundle\MagentoBundle\Entity\Address;
use Oro\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface;
use Oro\Bundle\MagentoBundle\Service\CustomerStateHandler;

class CustomerAddressExportProcessor extends StepExecutionAwareExportProcessor
{
    const CUSTOMER_ADDRESS_ID_KEY = 'customer_address_id';
    const ADDRESS_NOT_EXISTS_CODE = '103';

    /** @var MagentoTransportInterface */
    protected $transport;

    /** @var CustomerStateHandler */
    protected $stateHandler;

    /**
     * @param MagentoTransportInterface $transport
     */
    public function setTransport(MagentoTransportInterface $transport)
    {
        $this->transport = $transport;
    }

    /**
     * @param CustomerStateHandler $stateHandler
     */
    public function setStateHandler(CustomerStateHandler $stateHandler)
    {
        $this->stateHandler = $stateHandler;
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidArgumentException
     */
    public function process($object)
    {
        /** @var Address $object */
        $this->assertValidObject($object);
        if ($this->isAddressRemoved($object)) {
            //remove it also from local customer
            $owner = $object->getOwner();
            if ($owner) {
                $owner->getAddresses()->removeElement($object);
            }

            // stop processing removed item
            return null;
        }

        return parent::process($object);
    }

    /**
     * Checks that address exists on Magento side
     *
     * @param Address $address
     *
     * @return bool
     */
    protected function isAddressRemoved(Address $address)
    {
        if (empty($address->getOriginId())) {
            return false;
        }
        $this->transport->init($address->getChannel()->getTransport());
        try {
            //@TODO The necessity of this api query should be rechecked in CRM-8380
            $addressData = $this->transport->getCustomerAddressInfo($address->getOriginId());
        } catch (TransportException $e) {
            if ($e->getFaultCode() === self::ADDRESS_NOT_EXISTS_CODE) {
                return true;
            }
        }

        return !isset($addressData[self::CUSTOMER_ADDRESS_ID_KEY]) ||
               $addressData[self::CUSTOMER_ADDRESS_ID_KEY] !== $address->getOriginId();
    }

    /**
     * @param mixed $object
     *
     * @throws InvalidArgumentException
     */
    protected function assertValidObject($object)
    {
        if (!$object instanceof Address) {
            throw new InvalidArgumentException(
                sprintf(
                    'Expected instance of %s, "%s" given.',
                    Address::class,
                    is_object($object) ? get_class($object) : gettype($object)
                )
            );
        }
    }
}
