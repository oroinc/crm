<?php

namespace Oro\Bundle\ContactBundle\Tests\Functional\Api;

use Oro\Bundle\ApiBundle\Tests\Functional\RestJsonApiTestCase;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Tests\Functional\Api\DataFixtures\LoadContactsData;

class ContactApiTest extends RestJsonApiTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->loadFixtures([LoadContactsData::class]);
    }

    public function testGet()
    {
        $response = $this->get(
            ['entity' => 'contacts', 'id' => $this->getReference('contact1')->getId()]
        );

        $this->assertResponseContains('contact_get.yml', $response);
    }

    public function testPatchBirthday()
    {
        $contactId = $this->getReference('contact1')->getId();
        $response = $this->patch(
            ['entity' => 'contacts', 'id' => $contactId],
            [
                'data' => [
                    'type'       => 'contacts',
                    'id'         => (string)$contactId,
                    'attributes' => [
                        'birthday' => '1995-05-25'
                    ]
                ]
            ]
        );

        $this->assertResponseContains('contact_patch_birthday.yml', $response);

        // test that the birthday was changed
        $contact = $this->getEntityManager()->find(Contact::class, $contactId);
        self::assertEquals(new \DateTime('1995-05-25', new \DateTimeZone('UTC')), $contact->getBirthday());
    }
}
