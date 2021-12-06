<?php

namespace Oro\Bundle\ContactBundle\Tests\Functional\Api\Rest;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class RestContactAddressApiTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient([], $this->generateWsseAuthHeader());
        $this->loadFixtures(['@OroContactBundle/Tests/Functional/DataFixtures/contact_addresses.yml']);
    }

    public function testGetList()
    {
        $contactId = $this->getReference('Contact_Brenda')->getId();

        $this->client->jsonRequest(
            'GET',
            $this->getUrl('oro_api_get_contact_addresses', ['contactId' => $contactId])
        );
        $result = $this->client->getResponse();
        $result = json_decode($result->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertArrayHasKey(0, $result);
        $this->assertCount(1, $result);
        $expected = [
            'primary'        => true,
            'label'          => 'Address 1',
            'street'         => 'Street 1',
            'street2'        => 'Street 2',
            'city'           => 'Los Angeles',
            'postalCode'     => '90001',
            'country'        => 'United States',
            'region'         => 'California',
            'organization'   => 'Acme',
            'namePrefix'     => 'Mr.',
            'nameSuffix'     => 'M.D.',
            'firstName'      => 'John',
            'middleName'     => 'Edgar',
            'lastName'       => 'Doo',
            'types'          => [
                ['name' => 'billing', 'label' => 'Billing']
            ],
            'countryIso2'    => 'US',
            'countryIso3'    => 'US',
            'regionCode'     => 'CA',
            'customField1'   => 'val1',
            'custom_field_2' => 'val2'
        ];
        foreach ($expected as $key => $value) {
            self::assertEquals($value, $result[0][$key]);
        }
    }
}
