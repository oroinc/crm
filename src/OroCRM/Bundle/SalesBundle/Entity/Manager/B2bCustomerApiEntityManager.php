<?php

namespace OroCRM\Bundle\SalesBundle\Entity\Manager;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\AddressBundle\Utils\AddressApiUtils;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;
use Oro\Bundle\SoapBundle\Entity\Manager\EntitySerializerManagerInterface;
use Oro\Bundle\SoapBundle\Event\FindAfter;
use Oro\Bundle\SoapBundle\Serializer\EntitySerializer;

class B2bCustomerApiEntityManager extends ApiEntityManager implements EntitySerializerManagerInterface
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
    public function serialize(QueryBuilder $qb)
    {
        return $this->entitySerializer->serialize($qb, $this->getSerializationConfig());
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
        $entity = $qb->getQuery()->getResult();
        if (!$entity) {
            return null;
        }

        // dispatch oro_api.request.find.after event
        $event = new FindAfter($entity[0]);
        $this->eventDispatcher->dispatch(FindAfter::NAME, $event);

        $serialized = $this->entitySerializer->serializeEntities((array)$entity, $this->class, $config);

        return $serialized[0];
    }

    /**
     * @return array
     */
    protected function getSerializationConfig()
    {
        $config = [
            'fields' => [
                'shippingAddress' => AddressApiUtils::getAddressConfig(),
                'billingAddress'  => AddressApiUtils::getAddressConfig(),
                'account'         => ['fields' => 'id'],
                'contact'         => ['fields' => 'id'],
                'leads'           => ['fields' => 'id'],
                'opportunities'   => ['fields' => 'id'],
                'owner'           => ['fields' => 'id'],
                'organization'    => ['fields' => 'name'],
                'createdBy'       => ['fields' => 'id'],
                'updatedBy'       => ['fields' => 'id']
            ]
        ];

        return $config;
    }
}
