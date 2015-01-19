<?php

namespace OroCRM\Bundle\ContactBundle\Entity\Manager;

use Doctrine\Common\Persistence\ObjectManager;

use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;
use Oro\Bundle\SoapBundle\Entity\Manager\EntitySerializerManagerInterface;
use Oro\Bundle\SoapBundle\Serializer\EntitySerializer;

class ContactApiEntityManager extends ApiEntityManager implements EntitySerializerManagerInterface
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
     * @return array
     */
    protected function getSerializationConfig()
    {
        $config = [
            'excluded_fields' => ['email'],
            'fields'          => [
                'source'       => ['fields' => 'name'],
                'method'       => ['fields' => 'name'],
                'assignedTo'   => ['fields' => 'id'],
                'owner'        => ['fields' => 'id'],
                'reportsTo'    => ['fields' => 'id'],
                'createdBy'    => ['fields' => 'id'],
                'updatedBy'    => ['fields' => 'id'],
                'organization' => ['fields' => 'name'],
                'emails'       => [
                    'exclusion_policy' => 'all',
                    'fields'           => [
                        'email'   => null,
                        'primary' => null
                    ],
                    'orderBy'          => [
                        'primary' => 'DESC'
                    ]
                ],
                'phones'       => [
                    'exclusion_policy' => 'all',
                    'fields'           => [
                        'phone'   => null,
                        'primary' => null
                    ],
                    'orderBy'          => [
                        'primary' => 'DESC'
                    ]
                ],
                'addresses'    => [
                    'excluded_fields' => ['owner'],
                    'fields'          => [
                        'country' => ['fields' => 'name'],
                        'region'  => ['fields' => 'name'],
                        'types'   => ['fields' => 'name'],
                    ],
                    'post_serialize'  => function (array &$result) {
                        $this->postSerializeAddress($result);
                    }
                ],
                'groups'       => [
                    'fields' => [
                        'organization' => ['fields' => 'name'],
                        'owner'        => ['fields' => 'username']
                    ]
                ],
                'accounts'     => ['fields' => 'id']
            ],
            'post_serialize'  => function (array &$result) {
                $this->postSerializeContact($result);
            }
        ];

        return $config;
    }

    /**
     * @param array $result
     */
    protected function postSerializeContact(array &$result)
    {
        // @todo: an 'email' field is added only for backward compatibility with previous API
        $email = null;
        if (!empty($result['emails'])) {
            foreach ($result['emails'] as $item) {
                if ($item['primary']) {
                    $email = $item['email'];
                    break;
                }
            }
        }
        $result['email'] = $email;
    }

    /**
     * @param array $result
     */
    protected function postSerializeAddress(array &$result)
    {
        // @todo: just a temporary workaround until new API is implemented
        // the normal solution can be to use region_name virtual field and
        // exclusion rule declared in oro/entity.yml
        // - for 'region' field use a region text if filled; otherwise, use region name
        // - remove regionText field from a result
        if (!empty($result['regionText'])) {
            $result['region'] = $result['regionText'];
        }
        unset($result['regionText']);
    }
}
