<?php

namespace OroCRM\Bundle\MagentoBundle\Entity\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;
use Oro\Bundle\SoapBundle\Entity\Manager\EntitySerializerManagerInterface;
use Oro\Bundle\SoapBundle\Serializer\EntitySerializer;

use OroCRM\Bundle\MagentoBundle\Entity\Order;

class OrderApiEntityManager extends ApiEntityManager implements EntitySerializerManagerInterface
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
     * {@inheritdoc}
     */
    public function serializeOne($id)
    {
        $qb = $this->getRepository()->createQueryBuilder('e')
            ->where('e.id = :id')
            ->setParameter('id', $id);

        $config = $this->getSerializationConfig();

        $this->entitySerializer->prepareQuery($qb, $config);

        /** @var Order $entity */
        $entity = $qb->getQuery()->getOneOrNullResult();

        if (!$entity) {
            return null;
        }

        $serialized = $this->entitySerializer->serializeEntities([$entity], $this->class, $config);

        if (empty($serialized[0])) {
            return null;
        }
        $order = $serialized[0];

        $order['items']     = $this->getSerializedItems($entity);
        $order['addresses'] = $this->getSerializedAddresses($entity);

        return $order;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(QueryBuilder $qb)
    {
        return $this->entitySerializer->serialize($qb, $this->getSerializationConfig());
    }

    /**
     * @param Order $entity
     *
     * @return array
     */
    protected function getSerializedItems(Order $entity)
    {
        return $this->entitySerializer->serializeEntities(
            $entity->getItems()->toArray(),
            'OroCRM\Bundle\MagentoBundle\Entity\OrderItem',
            $this->getItemSerializationConfig()
        );
    }

    /**
     * @param Order $entity
     *
     * @return array
     */
    protected function getSerializedAddresses(Order $entity)
    {
        return $this->entitySerializer->serializeEntities(
            $entity->getAddresses()->toArray(),
            'OroCRM\Bundle\MagentoBundle\Entity\OrderAddress',
            $this->getAddressSerializationConfig()
        );
    }

    /**
     * @return array
     */
    protected function getSerializationConfig()
    {
        return [
            'excluded_fields' => ['workflowItem', 'workflowStep'],
            'fields'          => [
                'store'        => ['fields' => 'id'],
                'dataChannel'  => ['fields' => 'id'],
                'channel'      => ['fields' => 'id'],
                'cart'         => ['fields' => 'id'],
                'customer'     => ['fields' => 'id'],
                'owner'        => ['fields' => 'id'],
                'organization' => ['fields' => 'id']
            ]
        ];
    }

    /**
     * @return array
     */
    protected function getItemSerializationConfig()
    {
        return [
            'fields' => [
                'order' => ['fields' => 'id']
            ]
        ];
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
                'owner'   => ['fields' => 'id']
            ]
        ];
    }
}
