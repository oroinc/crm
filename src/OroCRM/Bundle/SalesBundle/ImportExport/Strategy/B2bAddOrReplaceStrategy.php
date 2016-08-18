<?php

namespace OroCRM\Bundle\SalesBundle\ImportExport\Strategy;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

use Oro\Bundle\ImportExportBundle\Strategy\Import\ConfigurableAddOrReplaceStrategy;
use Oro\Bundle\AddressBundle\Entity\Address;

use OroCRM\Bundle\SalesBundle\Entity\B2bCustomer;

class B2bAddOrReplaceStrategy extends ConfigurableAddOrReplaceStrategy
{
    /**
     * @var PropertyAccessor
     */
    protected $propertyAccessor;

    /**
     * @param PropertyAccessor $propertyAccessor
     */
    public function setPropertyAccessor(PropertyAccessor $propertyAccessor)
    {
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * {@inheritdoc}
     */
    protected function beforeProcessEntity($entity)
    {
        /** @var B2bCustomer $entity */
        $entity = parent::beforeProcessEntity($entity);

        // manually set empty addresses to skip merge from existing entities
        $itemData = $this->context->getValue('itemData');

        $addresses = ['shippingAddress', 'billingAddress'];
        foreach ($addresses as $addressName) {
            /** @var Address $address */
            $address = $this->getPropertyAccessor()->getValue($entity, $addressName);
            if (!$address) {
                $itemData[$addressName] = null;
                $this->context->setValue('itemData', $itemData);
            }
        }

        return $entity;
    }

    /**
     * @return PropertyAccessor
     */
    protected function getPropertyAccessor()
    {
        if (!$this->propertyAccessor) {
            $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
        }
        return $this->propertyAccessor;
    }
}
