<?php

namespace Oro\Bundle\ContactBundle\Tests\Functional\Api\Rest;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class RestContactGroupsApiTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient([], self::generateWsseAuthHeader());
    }

    public function testCreateContactGroup(): array
    {
        $request = [
            'contact_group' => [
                'label' => 'Contact_Group_Name_' . mt_rand(),
                'owner' => '1'
            ]
        ];
        $this->client->jsonRequest('POST', $this->getUrl('oro_api_post_contactgroup'), $request);
        $result = $this->client->getResponse();
        self::assertJsonResponseStatusCodeEquals($result, 201);

        return $request;
    }

    /**
     * @depends testCreateContactGroup
     */
    public function testGetContactGroup(array $request): array
    {
        $this->client->jsonRequest(
            'GET',
            $this->getUrl('oro_api_get_contactgroups')
        );
        $result = $this->client->getResponse();
        $result = json_decode($result->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $flag = 1;
        foreach ($result as $group) {
            if ($group['label'] === $request['contact_group']['label']) {
                $flag = 0;
                break;
            }
        }
        $this->assertEquals(0, $flag);

        $this->client->jsonRequest(
            'GET',
            $this->getUrl('oro_api_get_contactgroup', ['id' => $group['id']])
        );
        $result = $this->client->getResponse();
        self::assertJsonResponseStatusCodeEquals($result, 200);

        return $group;
    }

    /**
     * @depends testGetContactGroup
     * @depends testCreateContactGroup
     */
    public function testUpdateContactGroup(array $group, array $request)
    {
        $group['label'] .= '_Updated';
        $this->client->jsonRequest(
            'PUT',
            $this->getUrl('oro_api_put_contactgroup', ['id' => $group['id']]),
            $request
        );
        $result = $this->client->getResponse();
        self::assertEmptyResponseStatusCodeEquals($result, 204);

        $this->client->jsonRequest(
            'GET',
            $this->getUrl('oro_api_get_contactgroup', ['id' => $group['id']])
        );
        $result = $this->client->getResponse();
        self::assertJsonResponseStatusCodeEquals($result, 200);
        $result = json_decode($result->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertEquals($request['contact_group']['label'], $result['label'], 'ContactGroup does not updated');
    }

    /**
     * @depends testGetContactGroup
     */
    public function testDeleteContact(array $group)
    {
        $this->client->jsonRequest(
            'DELETE',
            $this->getUrl('oro_api_delete_contactgroup', ['id' => $group['id']])
        );
        $result = $this->client->getResponse();
        self::assertEmptyResponseStatusCodeEquals($result, 204);

        $this->client->jsonRequest(
            'GET',
            $this->getUrl('oro_api_get_contactgroup', ['id' => $group['id']])
        );
        $result = $this->client->getResponse();
        self::assertJsonResponseStatusCodeEquals($result, 404);
    }
}
