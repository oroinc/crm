<?php

namespace OroCRM\Bundle\MagentoBundle\Entity\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;
use Oro\Bundle\SoapBundle\Entity\Manager\EntitySerializerManagerInterface;
use Oro\Bundle\SoapBundle\Serializer\EntitySerializer;

use OroCRM\Bundle\MagentoBundle\Entity\OrderItem;

class OrderItemApiEntityManager extends ApiEntityManager implements EntitySerializerManagerInterface
{
    /** @var EntitySerializer */
    protected $entitySerializer;

    /**
     * @param string           $class
     * @param ObjectManager    $om
     * @param EntitySerializer $entitySerializer
     */
    public function __construct($class, ObjectManager $om, EntitySerializer $entitySerializer)
    {
        parent::__construct($class, $om);
        $this->entitySerializer = $entitySerializer;
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

        return $this->serialize($qb);
    }

    /**
     * @param int $orderId
     * @param int $orderItemId
     *
     * @return array|null
     */
    public function getSpecificSerializedItem($orderId, $orderItemId)
    {
        /** @var QueryBuilder $qb */
        $qb = $this->getBaseQB($orderId)
            ->andWhere('e.id = :orderItemId')
            ->setParameter('orderItemId', $orderItemId)
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
        return $this->entitySerializer->serialize($qb, ['fields' => ['order' => ['fields' => 'id']]]);
    }

    /**
     * @param array $criteria
     *
     * @return OrderItem
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
            ->andWhere('e.order = :orderId')
            ->setParameter('orderId', $orderId);
    }
}
