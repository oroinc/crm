<?php

namespace OroCRM\Bundle\SalesBundle\Autocomplete;

use OroCRM\Bundle\ChannelBundle\Autocomplete\ChannelLimitationHandler;
use OroCRM\Bundle\SalesBundle\Entity\B2bCustomer;
use Doctrine\ORM\QueryBuilder;

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
                $result[$property] = $this->getCustomerName($item);
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
    protected function getCustomerName(B2bCustomer $entity)
    {
        $customerName = $entity->getName();
        $accountName  = $entity->getAccount() ? $entity->getAccount()->getName() : $customerName;

        if ($accountName === $customerName) {
            return $customerName;
        }
        
        return sprintf('%s (%s)', $customerName, $accountName);
    }

    /**
     * @param array $entityIds
     * @return array
     */
    protected function getEntitiesByIds(array $entityIds)
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->entityRepository->createQueryBuilder('c');
        $queryBuilder->select('c', 'account');
        $queryBuilder->leftJoin('c.account', 'account');
        $queryBuilder->where($queryBuilder->expr()->in('c.' . $this->idFieldName, $entityIds));

        return $queryBuilder->getQuery()->getResult();
    }
}
