<?php

namespace Oro\Bundle\MagentoBundle\Entity\Manager;

use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\MagentoBundle\Entity\CartItem;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;

class CartItemApiEntityManager extends ApiEntityManager
{
    /**
     * @param int $cartId
     *
     * @return array|null
     */
    public function getAllSerializedItems($cartId)
    {
        /** @var QueryBuilder $qb */
        $qb = $this->getBaseQB($cartId);

        return $this->serialize($qb);
    }

    /**
     * @param int $cartId
     * @param int $cartItemId
     *
     * @return array|null
     */
    public function getSpecificSerializedItem($cartId, $cartItemId)
    {
        /** @var QueryBuilder $qb */
        $qb = $this->getBaseQB($cartId)
            ->andWhere('e.id = :cartItemId')
            ->setParameter('cartItemId', $cartItemId)
            ->setMaxResults(1);

        $result = $this->serialize($qb);

        return empty($result[0]) ? null : $result[0];
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
     * @return CartItem
     */
    public function findOneBy(array $criteria = [])
    {
        return $this->om->getRepository($this->class)->findOneBy($criteria);
    }

    /**
     * @param int $cartId
     *
     * @return QueryBuilder
     */
    protected function getBaseQB($cartId)
    {
        return $this->getRepository()->createQueryBuilder('e')
            ->andWhere('e.cart = :cartId')
            ->setParameter('cartId', $cartId);
    }

    /**
     * @return array
     */
    protected function getSerializationConfig()
    {
        return [
            'fields' => [
                'cart'    => ['fields' => 'id'],
                'channel' => ['fields' => 'id']
            ]
        ];
    }
}
