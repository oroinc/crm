<?php

namespace Oro\Bundle\ContactBundle\Tests\Functional\Api\Rest;

use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Entity\Group;
use Oro\Bundle\ContactBundle\Tests\Functional\DataFixtures\LoadAccountData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\UserBundle\Entity\User;

class RestContactApiTest extends WebTestCase
{
    private array $testAddress = [
        'street'     => 'contact_street',
        'city'       => 'contact_city',
        'country'    => 'US',
        'region'     => 'US-FL',
        'postalCode' => '12345',
        'primary'    => true,
        'types'      => [AddressType::TYPE_BILLING, AddressType::TYPE_SHIPPING],
    ];

    protected function setUp(): void
    {
        $this->initClient([], $this->generateWsseAuthHeader());
        $this->loadFixtures([LoadAccountData::class]);
    }

    public function testCreateContact(): array
    {
        /** @var Account $account */
        $account = $this->getReference('Account_first');
        $contactGroup = $this->getContactGroup();
        $contactGroupIds = $contactGroup ? [$contactGroup->getId()] : [];
        $user = $this->getUser();

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
        $this->client->jsonRequest('POST', $this->getUrl('oro_api_post_contact'), $request);

        $contact = $this->getJsonResponseContent($this->client->getResponse(), 201);

        $this->assertArrayHasKey('id', $contact);
        $this->assertNotEmpty($contact['id']);

        $request['id'] = $contact['id'];

        return $request;
    }

    /**
     * @depends testCreateContact
     */
    public function testGetContact(array $request): array
    {
        $this->client->jsonRequest(
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
     * @depends testCreateContact
     */
    public function testContactsFiltering(array $requestData)
    {
        $baseUrl = $this->getUrl('oro_api_get_contacts');
        $this->client->jsonRequest('GET', $baseUrl . '?createdAt>2010-10-10T09:09:09+02:00&page=1');

        $entities = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertNotEmpty($entities);
        $this->assertCount(1, $entities);

        $entity = array_pop($entities);
        $this->assertEquals($requestData['contact']['firstName'], $entity['firstName']);
        $this->assertEquals($requestData['contact']['lastName'], $entity['lastName']);

        $this->client->jsonRequest('GET', $baseUrl . '?createdAt>2050-10-10T09:09:09+02:00&limit=20');

        $entities = $this->getJsonResponseContent($this->client->getResponse(), 200);
        $this->assertEmpty($entities);

        $this->client->jsonRequest('GET', $baseUrl . '?ownerId=' . $requestData['contact']['owner']);
        $this->assertCount(1, $this->getJsonResponseContent($this->client->getResponse(), 200));

        $this->client->jsonRequest('GET', $baseUrl . '?ownerId<>' . $requestData['contact']['owner']);
        $this->assertEmpty($this->getJsonResponseContent($this->client->getResponse(), 200));

        $this->client->jsonRequest('GET', $baseUrl . '?ownerUsername=' . self::USER_NAME);
        $this->assertCount(1, $this->getJsonResponseContent($this->client->getResponse(), 200));

        $this->client->jsonRequest('GET', $baseUrl . '?ownerUsername<>' . self::USER_NAME);
        $this->assertEmpty($this->getJsonResponseContent($this->client->getResponse(), 200));

        $this->client->jsonRequest('GET', $baseUrl . '?assigneeId=' . $requestData['contact']['assignedTo']);
        $this->assertCount(1, $this->getJsonResponseContent($this->client->getResponse(), 200));

        $this->client->jsonRequest('GET', $baseUrl . '?assigneeId<>' . $requestData['contact']['assignedTo']);
        $this->assertEmpty($this->getJsonResponseContent($this->client->getResponse(), 200));

        $this->client->jsonRequest('GET', $baseUrl . '?assigneeUsername=' . self::USER_NAME);
        $this->assertCount(1, $this->getJsonResponseContent($this->client->getResponse(), 200));

        $this->client->jsonRequest('GET', $baseUrl . '?assigneeUsername<>' . self::USER_NAME);
        $this->assertEmpty($this->getJsonResponseContent($this->client->getResponse(), 200));

        $this->client->jsonRequest('GET', $baseUrl . '?phone=' . $requestData['contact']['phones'][0]['phone']);
        $this->assertCount(1, $this->getJsonResponseContent($this->client->getResponse(), 200));

        $this->client->jsonRequest('GET', $baseUrl . '?phone<>' .$requestData['contact']['phones'][0]['phone']);
        $this->assertEmpty($this->getJsonResponseContent($this->client->getResponse(), 200));
    }

    /**
     * @depends testContactsFiltering
     */
    public function testCreateContactWhenCreatedAtHasMillisecondsAndTimezone()
    {
        $request = [
            'contact' => [
                'firstName' => 'Contact_fname_' . mt_rand(),
                'createdAt' => '2014-03-04T20:00:00.123+01:00'
            ]
        ];
        $this->client->jsonRequest('POST', $this->getUrl('oro_api_post_contact'), $request);

        $this->getEntityManager()->clear();
        $responseContent = $this->getJsonResponseContent($this->client->getResponse(), 201);
        $contactId = $responseContent['id'];

        /** @var Contact $contact */
        $contact = $this->getEntityManager()->find(Contact::class, $contactId);

        self::assertEquals(
            '2014-03-04T19:00:00.000+0000',
            $contact->getCreatedAt()->format("Y-m-d\TH:i:s.vO")
        );
    }

    /**
     * @depends testContactsFiltering
     */
    public function testCreateContactWhenCreatedAtHasMillisecondsAndZTimezone()
    {
        $request = [
            'contact' => [
                'firstName' => 'Contact_fname_' . mt_rand(),
                'createdAt' => '2014-03-04T20:00:00.123Z'
            ]
        ];
        $this->client->jsonRequest('POST', $this->getUrl('oro_api_post_contact'), $request);

        $this->getEntityManager()->clear();
        $responseContent = $this->getJsonResponseContent($this->client->getResponse(), 201);
        $contactId = $responseContent['id'];

        /** @var Contact $contact */
        $contact = $this->getEntityManager()->find(Contact::class, $contactId);

        self::assertEquals(
            '2014-03-04T20:00:00.000+0000',
            $contact->getCreatedAt()->format("Y-m-d\TH:i:s.vO")
        );
    }

    /**
     * @depends testGetContact
     * @depends testCreateContact
     */
    public function testUpdateContact(array $contact, array $request)
    {
        /** @var Account $account */
        $account = $this->getReference('Account_second');
        $this->testAddress['types'] = ['billing'];

        $request['contact']['firstName'] .= '_Updated';
        $request['contact']['addresses'][0]['types']   = $this->testAddress['types'];
        $request['contact']['addresses'][0]['primary'] = true;
        $request['contact']['accounts'] = [$account->getId()];
        $request['contact']['reportsTo'] = $contact['id'];

        $this->client->jsonRequest('PUT', $this->getUrl('oro_api_put_contact', ['id' => $contact['id']]), $request);
        $result = $this->client->getResponse();
        $this->assertEmptyResponseStatusCodeEquals($result, 204);

        $this->client->jsonRequest('GET', $this->getUrl('oro_api_get_contact', ['id' => $contact['id']]));

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
     * @depends testGetContact
     */
    public function testDeleteContact(array $contact)
    {
        $this->client->jsonRequest('DELETE', $this->getUrl('oro_api_delete_contact', ['id' => $contact['id']]));
        $result = $this->client->getResponse();
        $this->assertEmptyResponseStatusCodeEquals($result, 204);

        $this->client->jsonRequest('GET', $this->getUrl('oro_api_get_contact', ['id' => $contact['id']]));
        $result = $this->client->getResponse();
        $this->assertJsonResponseStatusCodeEquals($result, 404);
    }

    private function assertAddresses(array $actualAddresses)
    {
        $this->assertCount(1, $actualAddresses);
        $address = current($actualAddresses);

        foreach (['types', 'street', 'city'] as $key) {
            $this->assertArrayHasKey($key, $address);
            $this->assertEquals($this->testAddress[$key], $address[$key]);
        }
    }

    private function getEntityManager(): EntityManagerInterface
    {
        return $this->getContainer()->get('doctrine')->getManager();
    }

    private function getContactGroup(): ?Group
    {
        $contactGroups = $this->getEntityManager()->getRepository(Group::class)->findAll();
        if (!$contactGroups) {
            return null;
        }

        return current($contactGroups);
    }

    private function getUser(): User
    {
        return $this->getEntityManager()->getRepository(User::class)->findOneByUsername(self::USER_NAME);
    }
}
