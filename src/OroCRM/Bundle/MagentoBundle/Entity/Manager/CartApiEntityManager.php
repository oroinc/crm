<?php

namespace OroCRM\Bundle\MagentoBundle\Entity\Manager;

use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;

use OroCRM\Bundle\MagentoBundle\Entity\Cart;
use OroCRM\Bundle\MagentoBundle\Entity\CartAddress;

class CartApiEntityManager extends ApiEntityManager
{
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

        /** @var Cart $entity */
        $entity = $qb->getQuery()->getOneOrNullResult();

        if (!$entity) {
            return null;
        }

        $serialized = $this->entitySerializer->serializeEntities([$entity], $this->class, $config);

        if (empty($serialized[0])) {
            return null;
        }

        $cart = $serialized[0];

        return $cart;
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
     * @return Cart
     */
    public function findOneBy(array $criteria = [])
    {
        return $this->om->getRepository($this->class)->findOneBy($criteria);
    }

    /**
     * @param CartAddress $entity
     *
     * @return array
     */
    protected function getSerializedAddresses(CartAddress $entity)
    {
        $result = $this->entitySerializer->serializeEntities(
            [$entity],
            'OroCRM\Bundle\MagentoBundle\Entity\CartAddress',
            $this->getAddressSerializationConfig()
        );

        return empty($result[0]) ? null : $result[0];
    }

    /**
     * @return array
     */
    protected function getAddressSerializationConfig()
    {
        return [
            'fields' => [
                'country' => ['fields' => 'iso2Code'],
                'region'  => ['fields' => 'combinedCode'],
                'channel' => ['fields' => 'id']
            ]
        ];
    }

    /**
     * @return array
     */
    protected function getSerializationConfig()
    {
        return [
            'excluded_fields' => ['relatedEmails'],
            'fields'          => [
                'store'           => ['fields' => 'id'],
                'dataChannel'     => ['fields' => 'id'],
                'channel'         => ['fields' => 'id'],
                'status'          => ['fields' => 'name'],
                'customer'        => ['fields' => 'id'],
                'owner'           => ['fields' => 'id'],
                'organization'    => ['fields' => 'id'],
                'shippingAddress' => $this->getAddressSerializationConfig(),
                'billingAddress'  => $this->getAddressSerializationConfig(),
                'cartItems'       => ['fields' => 'id']
            ]
        ];
    }
}
