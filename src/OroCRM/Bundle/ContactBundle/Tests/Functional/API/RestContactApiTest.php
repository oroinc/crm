<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Functional\API;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use OroCRM\Bundle\ContactBundle\Entity\Group;
use OroCRM\Bundle\AccountBundle\Entity\Account;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class RestContactApiTest extends WebTestCase
{
    /** @var array */
    protected $testAddress = [
        'street'     => 'contact_street',
        'city'       => 'contact_city',
        'country'    => 'US',
        'region'     => 'US-FL',
        'postalCode' => '12345',
        'primary'    => true,
        'types'      => [AddressType::TYPE_BILLING, AddressType::TYPE_SHIPPING],
    ];

    protected function setUp()
    {
        $this->initClient([], $this->generateWsseAuthHeader());
        $this->loadFixtures([
            'OroCRM\Bundle\ContactBundle\Tests\Functional\DataFixtures\LoadAccountData'
        ]);
    }

    /**
     * @return array
     */
    public function testCreateContact()
    {
        /** @var Account $account */
        $account         = $this->getReference('Account_first');
        $contactGroup    = $this->getContactGroup();
        $contactGroupIds = $contactGroup ? [$contactGroup->getId()] : [];
        $user            = $this->getUser();

        $request = [
            'contact' => [
                'firstName'   => 'Contact_fname_' . mt_rand(),
                'lastName'    => 'Contact_lname',
                'description' => 'Contact description',
                'source'      => 'other',
                'owner'       => $user->getId(),
                'addresses'   => [$this->testAddress],
                'accounts'    => [$account->getId()],
                'groups'      => $contactGroupIds,
                'assignedTo'  => $user->getId(),
                'createdAt'   => '2014-03-04T20:00:00+00:00',
                'phones'      => [
                    [
                        'phone'   => '123-45-67',
                        'primary' => 1
                    ],
                ],
            ]
        ];
        $this->client->request('POST', $this->getUrl('oro_api_post_contact'), $request);

        $contact = $this->getJsonResponseContent($this->client->getResponse(), 201);

        $this->assertArrayHasKey('id', $contact);
        $this->assertNotEmpty($contact['id']);

        $request['id'] = $contact['id'];

        return $request;
    }

    /**
     * @param $request
     *
     * @depends testCreateContact
     * @return array
     */
    public function testGetContact($request)
    {
        $this->client->request(
            'GET',
            $this->getUrl('oro_api_get_contact', ['id' => $request['id']])
        );

        $selectedContact = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $fields = [
            'firstName',
            'lastName',
            'description',
            'source',
            'owner',
            'accounts',
            'assignedTo',
            'createdAt'
        ];
        $this->assertEquals(
            array_intersect_key($request['contact'], array_flip($fields)),
            array_intersect_key($selectedContact, array_flip($fields))
        );

        $this->assertArrayNotHasKey(
            'defaultInAccounts',
            $selectedContact,
            'Internal relationship to accounts must not be returned'
        );

        // assert addresses
        $this->assertArrayHasKey('addresses', $selectedContact);
        $this->assertAddresses($selectedContact['addresses']);

        // assert contact groups
        $this->assertArrayHasKey('groups', $selectedContact);
        $this->assertSameSize($request['contact']['groups'], $selectedContact['groups']);
        $actualGroups = [];
        foreach ($selectedContact['groups'] as $group) {
            $this->assertArrayHasKey('id', $group);
            $actualGroups[] = $group['id'];
        }
        $this->assertEquals($request['contact']['groups'], $actualGroups);

        return $selectedContact;
    }

    /**
     * @param array $requestData
     *
     * @depends testCreateContact
     * @return array
     */
    public function testContactsFiltering(array $requestData)
    {
        $baseUrl = $this->getUrl('oro_api_get_contacts');
        $this->client->request('GET', $baseUrl . '?createdAt>2010-10-10T09:09:09+02:00&page=1');

        $entities = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertNotEmpty($entities);
        $this->assertCount(1, $entities);

        $entity = array_pop($entities);
        $this->assertEquals($requestData['contact']['firstName'], $entity['firstName']);
        $this->assertEquals($requestData['contact']['lastName'], $entity['lastName']);

        $this->client->request('GET', $baseUrl . '?createdAt>2050-10-10T09:09:09+02:00&limit=20');

        $entities = $this->getJsonResponseContent($this->client->getResponse(), 200);
        $this->assertEmpty($entities);

        $this->client->request('GET', $baseUrl . '?ownerId=' . $requestData['contact']['owner']);
        $this->assertCount(1, $this->getJsonResponseContent($this->client->getResponse(), 200));

        $this->client->request('GET', $baseUrl . '?ownerId<>' . $requestData['contact']['owner']);
        $this->assertEmpty($this->getJsonResponseContent($this->client->getResponse(), 200));

        $this->client->request('GET', $baseUrl . '?ownerUsername=' . self::USER_NAME);
        $this->assertCount(1, $this->getJsonResponseContent($this->client->getResponse(), 200));

        $this->client->request('GET', $baseUrl . '?ownerUsername<>' . self::USER_NAME);
        $this->assertEmpty($this->getJsonResponseContent($this->client->getResponse(), 200));

        $this->client->request('GET', $baseUrl . '?assigneeId=' . $requestData['contact']['assignedTo']);
        $this->assertCount(1, $this->getJsonResponseContent($this->client->getResponse(), 200));

        $this->client->request('GET', $baseUrl . '?assigneeId<>' . $requestData['contact']['assignedTo']);
        $this->assertEmpty($this->getJsonResponseContent($this->client->getResponse(), 200));

        $this->client->request('GET', $baseUrl . '?assigneeUsername=' . self::USER_NAME);
        $this->assertCount(1, $this->getJsonResponseContent($this->client->getResponse(), 200));

        $this->client->request('GET', $baseUrl . '?assigneeUsername<>' . self::USER_NAME);
        $this->assertEmpty($this->getJsonResponseContent($this->client->getResponse(), 200));

        $this->client->request('GET', $baseUrl . '?phone=' . $requestData['contact']['phones'][0]['phone']);
        $this->assertCount(1, $this->getJsonResponseContent($this->client->getResponse(), 200));

        $this->client->request('GET', $baseUrl . '?phone<>' .$requestData['contact']['phones'][0]['phone']);
        $this->assertEmpty($this->getJsonResponseContent($this->client->getResponse(), 200));
    }

    /**
     * @param array $contact
     * @param array $request
     *
     * @depends testGetContact
     * @depends testCreateContact
     */
    public function testUpdateContact($contact, $request)
    {
        /** @var Account $account */
        $account         = $this->getReference('Account_second');
        $this->testAddress['types'] = ['billing'];

        $request['contact']['firstName'] .= "_Updated";
        $request['contact']['addresses'][0]['types']   = $this->testAddress['types'];
        $request['contact']['addresses'][0]['primary'] = true;
        $request['contact']['accounts']                = [$account->getId()];
        $request['contact']['reportsTo']               = $contact['id'];

        $this->client->request('PUT', $this->getUrl('oro_api_put_contact', ['id' => $contact['id']]), $request);
        $result = $this->client->getResponse();
        $this->assertEmptyResponseStatusCodeEquals($result, 204);

        $this->client->request('GET', $this->getUrl('oro_api_get_contact', ['id' => $contact['id']]));

        $contact = $this->getJsonResponseContent($this->client->getResponse(), 200);
        $this->assertEquals($request['contact']['firstName'], $contact['firstName'], 'Contact was not updated');

        // assert address
        $this->assertArrayHasKey('addresses', $contact);
        $this->assertAddresses($contact['addresses']);

        // assert related entities
        foreach (['accounts', 'reportsTo'] as $key) {
            $this->assertEquals($request['contact'][$key], $contact[$key]);
        }
    }

    /**
     * @param $contact
     *
     * @depends testGetContact
     */
    public function testDeleteContact($contact)
    {
        $this->client->request('DELETE', $this->getUrl('oro_api_delete_contact', ['id' => $contact['id']]));
        $result = $this->client->getResponse();
        $this->assertEmptyResponseStatusCodeEquals($result, 204);

        $this->client->request('GET', $this->getUrl('oro_api_get_contact', ['id' => $contact['id']]));
        $result = $this->client->getResponse();
        $this->assertJsonResponseStatusCodeEquals($result, 404);
    }

    /**
     * @param array $actualAddresses
     */
    protected function assertAddresses(array $actualAddresses)
    {
        $this->assertCount(1, $actualAddresses);
        $address = current($actualAddresses);

        foreach (['types', 'street', 'city'] as $key) {
            $this->assertArrayHasKey($key, $address);
            $this->assertEquals($this->testAddress[$key], $address[$key]);
        }
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->getContainer()->get('doctrine')->getManager();
    }

    /**
     * @return Group|null
     */
    protected function getContactGroup()
    {
        $contactGroups = $this->getEntityManager()->getRepository('OroCRMContactBundle:Group')->findAll();
        if (0 == count($contactGroups)) {
            return null;
        }

        return current($contactGroups);
    }

    /**
     * @return User
     */
    protected function getUser()
    {
        return $this->getEntityManager()->getRepository('OroUserBundle:User')->findOneByUsername(self::USER_NAME);
    }
}
