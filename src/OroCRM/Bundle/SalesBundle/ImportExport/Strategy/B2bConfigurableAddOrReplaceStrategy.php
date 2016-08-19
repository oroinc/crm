<?php

namespace OroCRM\Bundle\SalesBundle\ImportExport\Strategy;

use Oro\Bundle\AddressBundle\Entity\Address;
use Oro\Bundle\ImportExportBundle\Strategy\Import\ConfigurableAddOrReplaceStrategy;

use OroCRM\Bundle\SalesBundle\Entity\B2bCustomer;

class B2bConfigurableAddOrReplaceStrategy extends ConfigurableAddOrReplaceStrategy
{
    /**
     * Save state about exists Billing Address value in entity or not.
     * Value is updated in function beforeProcessEntity and used
     * in function afterProcessEntity
     *
     * @var bool
     */
    protected $isBillingAddress = true;

    /**
     * Save state about exists Shipping Address value in entity or not.
     * Value is updated in function beforeProcessEntity and used
     * in function afterProcessEntity
     *
     * @var bool
     */
    protected $isShippingAddress = true;

    /**
     * {@inheritdoc}
     */
    protected function beforeProcessEntity($entity)
    {
        /** @var B2bCustomer $entity */
        $entity = parent::beforeProcessEntity($entity);
        $this->checkEmptyAddresses($entity);

        return $entity;
    }

    /**
     * {@inheritdoc}
     */
    protected function afterProcessEntity($entity)
    {
        /** @var B2bCustomer $entity */
        $entity = parent::afterProcessEntity($entity);
        $this->clearEmptyAddresses($entity);

        $billingAddress = $entity->getBillingAddress();
        $this->guessRegion($billingAddress);
        $shippingAddress = $entity->getShippingAddress();
        $this->guessRegion($shippingAddress);

        return $entity;
    }

    /**
     * @param B2bCustomer $entity
     */
    protected function checkEmptyAddresses(B2bCustomer $entity)
    {
        if (!$entity->getBillingAddress()) {
            $this->isBillingAddress = false;
        }

        if (!$entity->getShippingAddress()) {
            $this->isShippingAddress = false;
        }
    }

    /**
     * @param B2bCustomer $entity
     */
    protected function clearEmptyAddresses(B2bCustomer $entity)
    {
        if (!$this->isBillingAddress) {
            $entity->setBillingAddress(null);
        }

        if (!$this->isShippingAddress) {
            $entity->setShippingAddress(null);
        }
    }

    /**
     * @param Address $address
     */
    protected function guessRegion($address)
    {
        if ($address
            && $address->getCountry() && $address->getRegionText()
            && !$address->getRegion()
        ) {
            $region = $this->doctrineHelper
                ->getEntityRepository('OroAddressBundle:Region')
                ->findOneBy(
                    [
                        'country' => $address->getCountry(),
                        'name'    => $address->getRegionText()
                    ]
                );
            if ($region) {
                $address->setRegion($region);
                $address->setRegionText(null);
            }
        }
    }
}
