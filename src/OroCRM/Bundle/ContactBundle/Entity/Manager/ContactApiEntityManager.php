<?php

namespace OroCRM\Bundle\ContactBundle\Entity\Manager;

use Oro\Bundle\AddressBundle\Utils\AddressApiUtils;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;

class ContactApiEntityManager extends ApiEntityManager
{
    /**
     * {@inheritdoc}
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
                'addresses'    => AddressApiUtils::getAddressConfig(true),
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
}
