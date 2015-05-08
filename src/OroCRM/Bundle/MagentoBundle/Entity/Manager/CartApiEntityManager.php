<?php

namespace OroCRM\Bundle\MagentoBundle\Entity\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;
use Oro\Bundle\SoapBundle\Entity\Manager\EntitySerializerManagerInterface;
use Oro\Bundle\SoapBundle\Serializer\EntitySerializer;

use OroCRM\Bundle\MagentoBundle\Entity\Cart;
use OroCRM\Bundle\MagentoBundle\Entity\CartAddress;

class CartApiEntityManager extends ApiEntityManager implements EntitySerializerManagerInterface
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

        $cart['cartItems']       = $this->getSerializedItems($entity);
        $cart['shippingAddress'] = $this->getSerializedAddresses($entity->getShippingAddress());
        $cart['billingAddress']  = $this->getSerializedAddresses($entity->getBillingAddress());

        return $cart;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(QueryBuilder $qb)
    {
        return $this->entitySerializer->serialize($qb, []);
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
     * @param Cart $entity
     *
     * @return array
     */
    protected function getSerializedItems(Cart $entity)
    {
        return $this->entitySerializer->serializeEntities(
            $entity->getCartItems()->toArray(),
            'OroCRM\Bundle\MagentoBundle\Entity\CartItem',
            $this->getItemSerializationConfig()
        );
    }

    /**
     * @return array
     */
    protected function getItemSerializationConfig()
    {
        return [
            'fields' => [
                'cart' => ['fields' => 'id']
            ]
        ];
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
                'region'  => ['fields' => 'combinedCode']
            ]
        ];
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
                'status'       => ['fields' => 'name'],
                'customer'     => ['fields' => 'id'],
                'owner'        => ['fields' => 'id'],
                'organization' => ['fields' => 'id']
            ]
        ];
    }
}
