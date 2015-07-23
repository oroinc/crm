<?php

namespace OroCRM\Bundle\MagentoBundle\Entity\Manager;

use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;

use OroCRM\Bundle\MagentoBundle\Entity\Address;

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
        /** @var QueryBuilder $qb */
        $qb = $this->getBaseQB($orderId);

        $addresses = $this->serialize($qb);

        foreach ($addresses as &$address) {
            $address['types'] = $this->getSerializedAddressTypes($address['types']);
        }
        return $addresses;
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

        $customerAddress          = $result[0];
        $customerAddress['types'] = $this->getSerializedAddressTypes($customerAddress['types']);

        return $customerAddress;
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
        $customerAddress          = $customerAddresses[0];
        $customerAddress['types'] = $this->getSerializedAddressTypes($entity->getTypes()->toArray());

        return $customerAddress;
    }

    /**
     * @param array $types
     *
     * @return array
     */
    protected function getSerializedAddressTypes(array $types)
    {
        $result = [];

        foreach ($types as $type) {
            $result[] = $type['name'];
        }

        return $result;
    }

    /**
     * @return array
     */
    protected function getSerializationConfig()
    {
        $config = [
            'fields' => [
                'owner'        => ['fields' => 'id'],
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
