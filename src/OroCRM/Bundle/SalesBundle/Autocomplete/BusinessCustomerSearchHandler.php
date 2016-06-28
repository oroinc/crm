<?php

namespace OroCRM\Bundle\SalesBundle\Autocomplete;

use OroCRM\Bundle\ChannelBundle\Autocomplete\ChannelLimitationHandler;
use OroCRM\Bundle\SalesBundle\Entity\B2bCustomer;

class BusinessCustomerSearchHandler extends ChannelLimitationHandler
{
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
            if ($property === 'name') {
                $result[$property] = $this->checkCustomerName($item);
            } else {
                $result[$property] = $this->getPropertyValue($property, $item);
            }
        }

        return $result;
    }

    /**
     * Returns customer name with account name in parentheses
     * if their names not identical.
     * Otherwise returns only customer name.
     *
     * @param B2bCustomer   $entity
     * @return string
     */
    protected function checkCustomerName(B2bCustomer $entity)
    {
        $accountName  = $entity->getAccount()->getName();
        $customerName = $entity->getName();

        if ($accountName === $customerName) {
            return $customerName;
        }

        return sprintf("%s (%s)", $customerName, $accountName);
    }

    /**
     * Get search results data by id
     *
     * @param int $query
     *
     * @return array
     */
    protected function findById($query)
    {
        return $this->getEntitiesByIds(explode(',', $query));
    }
}
