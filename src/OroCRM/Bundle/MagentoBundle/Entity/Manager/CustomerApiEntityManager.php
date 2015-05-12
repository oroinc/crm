<?php

namespace OroCRM\Bundle\MagentoBundle\Entity\Manager;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\AddressBundle\Utils\AddressApiUtils;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;
use Oro\Bundle\SoapBundle\Entity\Manager\EntitySerializerManagerInterface;
use Oro\Bundle\SoapBundle\Serializer\EntitySerializer;

use OroCRM\Bundle\MagentoBundle\Entity\Customer;

class CustomerApiEntityManager extends ApiEntityManager implements EntitySerializerManagerInterface
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
        $result = $this->entitySerializer->serialize($qb, $this->getSerializationAllItemsConfig());

        foreach ($result as &$row) {
            $date             = new \DateTime($row['birthday']);
            $row['birthday']  = $date->format('Y-m-d');
        }

        return $result;
    }

    /**
     * @param array $addresses
     *
     * @return array
     */
    protected function getSerializeAddress(array $addresses)
    {
        $result = [];

        foreach ($addresses as $address) {
            if ($address['owner'] instanceof Customer) {
                $address['owner'] = $address['owner']->getId();
            }

            $address['types'] = $this->getTypes($address['types']);

            $result[] = $address;
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function serializeOne($id)
    {
        $qb = $this->getRepository()
            ->createQueryBuilder('e')
            ->where('e.id = :id')
            ->setParameter('id', $id);

        $config = $this->getSerializationConfig();
        $this->entitySerializer->prepareQuery($qb, $config);

        /** @var Customer $entity */
        $entity = $qb->getQuery()->getOneOrNullResult();

        if (!$entity) {
            return null;
        }

        $serialized = $this->entitySerializer->serializeEntities([$entity], $this->class, $config);

        if (empty($serialized[0])) {
            return null;
        }

        /** @var Customer $customer */
        $customer              = $serialized[0];
        $customer['addresses'] = $this->getSerializedAddresses($entity);

        if ($entity->getBirthday()) {
            $customer['birthday'] = $entity->getBirthday()->format('Y-m-d');
        }

        foreach ($customer['addresses'] as &$address) {
            $address['types'] = $this->getTypes($address['types']);
        }

        return $customer;
    }

    /**
     * @param array $addressTypes
     *
     * @return array
     */
    protected function getTypes(array $addressTypes)
    {
        $types = [];

        foreach ($addressTypes as $type) {
            $types[] = $type['name'];
        }

        return $types;
    }

    /**
     * @return array
     */
    protected function getSerializationConfig()
    {
        return [
            'excluded_fields' => ['addresses', 'carts', 'orders', 'newsletterSubscriber'],
            'fields'          => [
                'website'      => ['fields' => 'id'],
                'store'        => ['fields' => 'id'],
                'group'        => ['fields' => 'id'],
                'contact'      => ['fields' => 'id'],
                'account'      => ['fields' => 'id'],
                'dataChannel'  => ['fields' => 'id'],
                'channel'      => ['fields' => 'id'],
                'owner'        => ['fields' => 'id'],
                'organization' => ['fields' => 'id']
            ]
        ];
    }

    /**
     * @param Customer $entity
     *
     * @return array
     */
    protected function getSerializedAddresses(Customer $entity)
    {
        return $this->entitySerializer->serializeEntities(
            $entity->getAddresses()->toArray(),
            'OroCRM\Bundle\MagentoBundle\Entity\Address',
            $this->getAddressSerializationConfig()
        );
    }

    /**
     * @return array
     */
    protected function getAddressSerializationConfig()
    {
        return [
            'excluded_fields' => ['newsletterSubscriber'],
            'fields'          => [
                'country' => ['fields' => 'iso2Code'],
                'region'  => ['fields' => 'combinedCode'],
                'owner'   => ['fields' => 'id'],
                'created' => ['fields' => 'date'],
                'updated' => ['fields' => 'date']
            ]
        ];
    }

    /**
     * @return array
     */
    protected function getSerializationAllItemsConfig()
    {
        return [
            'excluded_fields' => ['carts', 'orders', 'newsletterSubscriber'],
            'fields'          => [
                'website'      => ['fields' => 'id'],
                'store'        => ['fields' => 'id'],
                'group'        => ['fields' => 'id'],
                'contact'      => ['fields' => 'id'],
                'account'      => ['fields' => 'id'],
                'dataChannel'  => ['fields' => 'id'],
                'channel'      => ['fields' => 'id'],
                'owner'        => ['fields' => 'id'],
                'organization' => ['fields' => 'id'],
                'addresses'    => AddressApiUtils::getAddressConfig(true)
            ]
        ];
    }
}
