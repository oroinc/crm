<?php

namespace Oro\Bundle\MagentoBundle\Entity\Manager;

use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\MagentoBundle\Entity\Address;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;

class CustomerAddressApiEntityManager extends ApiEntityManager
{
    /**
     * {@inheritdoc}
     */
    public function serialize(QueryBuilder $qb)
    {
        return $this->entitySerializer->serialize($qb, $this->getSerializationConfig());
    }

    /**
     * @param int $orderId
     *
     * @return array|null
     */
    public function getAllSerializedItems($orderId)
    {
        return $this->serialize($this->getBaseQB($orderId));
    }

    /**
     * @param int $customerId
     * @param int $addressId
     *
     * @return mixed
     */
    public function serializeElement($customerId, $addressId)
    {
        $qb = $this->getBaseQB($customerId)
            ->andWhere('e.id = :addressId')
            ->setParameter('addressId', $addressId)
            ->setMaxResults(1);

        $result = $this->serialize($qb);

        if (!$result) {
            return null;
        }

        return $result[0];
    }

    /**
     * {@inheritdoc}
     */
    public function serializeOne($id)
    {
        $qb = $this->getRepository()->createQueryBuilder('e')
            ->where('e.id = :id')
            ->setParameter('id', $id);

        $config = $this->getSerializationConfig();
        $this->entitySerializer->prepareQuery($qb, $config);

        /** @var Address $entity */
        $entity = $qb->getQuery()->getOneOrNullResult();

        if (!$entity) {
            return null;
        }

        $customerAddresses = $this->entitySerializer->serializeEntities([$entity], $this->class, $config);

        if (empty($customerAddresses[0])) {
            return null;
        }

        return $customerAddresses[0];
    }

    /**
     * @return array
     */
    protected function getSerializationConfig()
    {
        $config = [
            'fields' => [
                'owner'        => ['fields' => 'id'],
                'types'        => ['fields' => 'name'],
                'country'      => ['fields' => 'iso2Code'],
                'region'       => ['fields' => 'combinedCode'],
                'organization' => ['fields' => 'id']
            ]
        ];

        return $config;
    }

    /**
     * @param int $customerId
     *
     * @return QueryBuilder
     */
    protected function getBaseQB($customerId)
    {
        return $this->getRepository()->createQueryBuilder('e')
            ->andWhere('e.owner = :customerId')
            ->setParameter('customerId', $customerId);
    }

    /**
     * @param array $criteria
     *
     * @return Address
     */
    public function findOneBy(array $criteria = [])
    {
        return $this->om->getRepository($this->class)->findOneBy($criteria);
    }
}
