<?php

namespace Oro\Bundle\MagentoBundle\Entity\Manager;

use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\MagentoBundle\Entity\OrderAddress;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;

class OrderAddressApiEntityManager extends ApiEntityManager
{
    /**
     * @param int $orderId
     *
     * @return array|null
     */
    public function getAllSerializedItems($orderId)
    {
        /** @var QueryBuilder $qb */
        $qb = $this->getBaseQB($orderId);

        return $this->serialize($qb);
    }

    /**
     * @param int $orderId
     * @param int $addressId
     *
     * @return mixed
     */
    public function serializeElement($orderId, $addressId)
    {
        $qb = $this->getRepository()->createQueryBuilder('e')
            ->andWhere('e.id = :addressId')
            ->andWhere('e.owner = :orderId')
            ->setParameter('orderId', $orderId)
            ->setParameter('addressId', $addressId)
            ->setMaxResults(1);

        $result = $this->serialize($qb);

        return empty($result[0])? null : $result[0];
    }

    /**
     * {@inheritdoc}
     */
    public function serializeOne($id)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(QueryBuilder $qb)
    {
        return $this->entitySerializer->serialize($qb, $this->getSerializationConfig());
    }

    /**
     * @param array $criteria
     *
     * @return OrderAddress
     */
    public function findOneBy(array $criteria = [])
    {
        return $this->om->getRepository($this->class)->findOneBy($criteria);
    }

    /**
     * @param int $orderId
     *
     * @return QueryBuilder
     */
    protected function getBaseQB($orderId)
    {
        return $this->getRepository()->createQueryBuilder('e')
            ->andWhere('e.owner = :orderId')
            ->setParameter('orderId', $orderId);
    }

    /**
     * @return array
     */
    protected function getSerializationConfig()
    {
        return [
            'fields' => [
                'types'   => ['fields' => 'name'],
                'owner'   => ['fields' => 'id'],
                'channel' => ['fields' => 'id'],
            ],
        ];
    }
}
