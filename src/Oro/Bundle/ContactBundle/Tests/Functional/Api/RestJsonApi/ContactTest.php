<?php

namespace Oro\Bundle\ContactBundle\Tests\Functional\Api\RestJsonApi;

use Oro\Bundle\AddressBundle\Tests\Functional\Api\RestJsonApi\PrimaryEmailTestTrait;
use Oro\Bundle\AddressBundle\Tests\Functional\Api\RestJsonApi\PrimaryPhoneTestTrait;
use Oro\Bundle\ApiBundle\Tests\Functional\RestJsonApiTestCase;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Tests\Functional\Api\DataFixtures\LoadContactsData;

/**
 * @dbIsolationPerTest
 */
class ContactTest extends RestJsonApiTestCase
{
    use PrimaryEmailTestTrait;
    use PrimaryPhoneTestTrait;

    private const ENTITY_CLASS = Contact::class;
    private const ENTITY_TYPE = 'contacts';
    private const CREATE_MIN_REQUEST_DATA = 'create_contact_min.yml';
    private const ENTITY_WITHOUT_EMAILS_REF = 'contact2';
    private const ENTITY_WITH_EMAILS_REF = 'contact1';
    private const PRIMARY_EMAIL = 'contact1_2@example.com';
    private const NOT_PRIMARY_EMAIL = 'contact1_1@example.com';
    private const ENTITY_WITHOUT_PHONES_REF = 'contact2';
    private const ENTITY_WITH_PHONES_REF = 'contact1';
    private const PRIMARY_PHONE = '5556661112';
    private const NOT_PRIMARY_PHONE = '5556661111';

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures([LoadContactsData::class]);
    }

    public function testGet()
    {
        $response = $this->get(
            ['entity' => 'contacts', 'id' => $this->getReference('contact1')->getId()]
        );

        $this->assertResponseContains('get_contact.yml', $response);
    }

    public function testGetListWithPrimaryEmailAndEmailsFieldsOnly()
    {
        $response = $this->cget(
            ['entity' => 'contacts', 'fields[contacts]' => 'primaryEmail,emails']
        );

        $expectedData = [
            'data' => [
                [
                    'type'       => 'contacts',
                    'id'         => '<toString(@contact1->id)>',
                    'attributes' => [
                        'primaryEmail' => 'contact1_2@example.com',
                        'emails'       => [
                            ['email' => 'contact1_1@example.com'],
                            ['email' => 'contact1_2@example.com']
                        ]
                    ]
                ],
                [
                    'type'       => 'contacts',
                    'id'         => '<toString(@contact2->id)>',
                    'attributes' => [
                        'primaryEmail' => null,
                        'emails'       => []
                    ]
                ]
            ]
        ];
        $this->assertResponseContains($expectedData, $response);
    }

    public function testGetListFilteredByPrimaryEmail()
    {
        $response = $this->cget(
            ['entity' => 'contacts', 'filter[primaryEmail]' => 'contact1_2@example.com']
        );

        $this->assertResponseContains('get_contacts_filter_by_primary_email.yml', $response);
    }

    public function testGetListFilteredByEmail()
    {
        $response = $this->cget(
            ['entity' => 'contacts', 'filter[emails]' => 'contact1_1@example.com']
        );

        $this->assertResponseContains('get_contacts_filter_by_email.yml', $response);
    }

    public function testUpdateBirthday()
    {
        $contactId = $this->getReference('contact1')->getId();

        $data = [
            'data' => [
                'type'       => 'contacts',
                'id'         => (string)$contactId,
                'attributes' => [
                    'birthday' => '1995-05-25'
                ]
            ]
        ];
        $response = $this->patch(
            ['entity' => 'contacts', 'id' => $contactId],
            $data
        );

        $expectedData = $data;
        $expectedData['data']['relationships']['updatedBy']['data'] = [
            'type' => 'users',
            'id'   => '<toString(@user->id)>'
        ];
        $this->assertResponseContains($expectedData, $response);

        // test that the birthday was changed
        $contact = $this->getEntityManager()->find(Contact::class, $contactId);
        self::assertEquals(new \DateTime('1995-05-25', new \DateTimeZone('UTC')), $contact->getBirthday());
    }

    public function testGetListWithDifferentPartialQueriesForSameEntityType()
    {
        $response = $this->cget(
            ['entity' => 'accounts'],
            [
                'filter[id]' => '<toString(@account1->id)>',
                'fields'     => [
                    'accounts' => 'defaultContact,contacts',
                    'contacts' => 'firstName'
                ],
                'include'    => 'contacts'
            ]
        );

        $this->assertResponseContains(
            [
                'data'     => [
                    [
                        'type'          => 'accounts',
                        'id'            => '<toString(@account1->id)>',
                        'relationships' => [
                            'contacts'       => [
                                'data' => [
                                    ['type' => 'contacts', 'id' => '<toString(@contact1->id)>'],
                                    ['type' => 'contacts', 'id' => '<toString(@contact2->id)>']
                                ]
                            ],
                            'defaultContact' => [
                                'data' => ['type' => 'contacts', 'id' => '<toString(@contact1->id)>']
                            ]
                        ]
                    ]
                ],
                'included' => [
                    [
                        'type'       => 'contacts',
                        'id'         => '<toString(@contact1->id)>',
                        'attributes' => [
                            'firstName' => 'Contact 1'
                        ]
                    ],
                    [
                        'type'       => 'contacts',
                        'id'         => '<toString(@contact2->id)>',
                        'attributes' => [
                            'firstName' => 'Contact 2'
                        ]
                    ]
                ]
            ],
            $response
        );
    }
}
