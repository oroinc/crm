<?php

namespace Oro\Bundle\ContactBundle\Tests\Functional\Api\RestPlain;

use Oro\Bundle\ApiBundle\Tests\Functional\RestPlainApiTestCase;

class ContactTest extends RestPlainApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures(['@OroContactBundle/Tests/Functional/Api/DataFixtures/contact_addresses.yml']);
    }

    public function testGet(): void
    {
        $response = $this->get(
            ['entity' => 'contacts', 'id' => $this->getReference('contact1')->getId()]
        );

        $this->assertResponseContains(
            [
                'id'        => '@contact1->id',
                'firstName' => 'Contact 1',
                'emails'    => [
                    ['email' => 'contact1_1@example.com'],
                    ['email' => 'contact1_2@example.com']
                ],
                'phones'    => [
                    ['phone' => '5556661111'],
                    ['phone' => '5556661112']
                ],
                'addresses' => [
                    [
                        'id'    => '@contact_address1->id',
                        'label' => 'Address 1',
                        'types' => ['billing']
                    ],
                    [
                        'id'    => '@contact_address2->id',
                        'label' => 'Address 2',
                        'types' => ['shipping']
                    ],
                    [
                        'id'    => '@contact_address3->id',
                        'label' => 'Address 3',
                        'types' => []
                    ]
                ]
            ],
            $response
        );
    }
}
