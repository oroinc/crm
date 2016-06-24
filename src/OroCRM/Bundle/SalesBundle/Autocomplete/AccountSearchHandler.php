<?php

namespace OroCRM\Bundle\SalesBundle\Autocomplete;

use OroCRM\Bundle\ChannelBundle\Autocomplete\ChannelLimitationHandler;
use OroCRM\Bundle\SalesBundle\Entity\B2bCustomer;

class AccountSearchHandler extends ChannelLimitationHandler
{

    const CUSTOMER_NAME_PROPERTY = 'name';

    /**
     * {@inheritdoc}
     */
    public function convertItem($item)
    {
        $result = [];

        if ($this->idFieldName) {
            $result[$this->idFieldName] = $this->getPropertyValue($this->idFieldName, $item);
        }

        foreach ($this->properties as $property) {
            if ($property === self::CUSTOMER_NAME_PROPERTY) {
                $result[$property] = $this->checkCustomerName($property, $item);
            } else {
                $result[$property] = $this->getPropertyValue($property, $item);
            }
        }

        return $result;
    }

    /**
     * Check if customer name is equal with account name
     * and return customer name with account if not equal
     *
     * @param string        $propertyPath
     * @param B2bCustomer   $entity
     * @return string
     */
    private function checkCustomerName($propertyPath, B2bCustomer $entity)
    {
        $accountName  = $entity->getAccount()->getName();
        $customerName = $entity->getName();

        if ($accountName === $customerName) {
            return $this->getPropertyValue($propertyPath, $entity);
        }

        return $customerName . '('.$accountName.')';
    }
}
