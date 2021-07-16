<?php

namespace Oro\Bundle\ContactBundle\Entity\Manager;

use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\AddressBundle\Utils\AddressApiUtils;
use Oro\Bundle\AttachmentBundle\Manager\AttachmentManager;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;

/**
 * The API manager for Contact entity.
 */
class ContactApiEntityManager extends ApiEntityManager
{
    /** @var  AttachmentManager */
    protected $attachmentManager;

    /**
     * @param string            $class
     * @param ObjectManager     $om
     * @param AttachmentManager $attachmentManager
     */
    public function __construct(
        $class,
        ObjectManager $om,
        AttachmentManager $attachmentManager
    ) {
        parent::__construct($class, $om);
        $this->attachmentManager = $attachmentManager;
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
            $result['picture'] = $this->attachmentManager->getFileRestApiUrl($result['picture']['id']);
        }

        return $result;
    }
}
