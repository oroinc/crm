<?php

namespace Oro\Bundle\ContactBundle\Entity\Manager;

use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\AddressBundle\Utils\AddressApiUtils;
use Oro\Bundle\AttachmentBundle\Entity\Manager\FileApiEntityManager;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;

/**
 * The API manager for Contact entity.
 */
class ContactApiEntityManager extends ApiEntityManager
{
    protected FileApiEntityManager $fileApiEntityManager;

    public function __construct(
        string $class,
        ObjectManager $om,
        FileApiEntityManager $fileApiEntityManager
    ) {
        parent::__construct($class, $om);
        $this->fileApiEntityManager = $fileApiEntityManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function getSerializationConfig()
    {
        $config = [
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
                // use "_email" as the field name to prevent calling of Contact::getEmail()
                // and removing "email" field is added in "post_serialize" handler from the result
                '_email'       => ['exclude' => true, 'property_path' => 'email'],
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
                'accounts'     => ['fields' => 'id'],
                'picture'      => ['fields' => 'id']
            ],
            'post_serialize'  => function (array $result) {
                return $this->postSerializeContact($result);
            }
        ];

        return $config;
    }

    protected function postSerializeContact(array $result): array
    {
        // an 'email' field is added only for backward compatibility with previous API
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

        if (!empty($result['picture'])) {
            $result['picture'] = $this->fileApiEntityManager->getFileRestApiUrl($result['picture']['id']);
        }

        return $result;
    }
}
