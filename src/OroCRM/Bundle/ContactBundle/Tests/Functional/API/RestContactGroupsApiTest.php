<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Functional\API;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class RestContactGroupsApiTest extends WebTestCase
{
    protected function setUp()
    {
        $this->initClient(array(), $this->generateWsseAuthHeader());
    }

    /**
     * @return array
     */
    public function testCreateContactGroup()
    {
        $request = array(
            "contact_group" => array(
                "label" => 'Contact_Group_Name_' . mt_rand(),
                "owner" => '1'
            )
        );
        $this->client->request('POST', $this->getUrl('oro_api_post_contactgroup'), $request);
        $result = $this->client->getResponse();
        $this->assertJsonResponseStatusCodeEquals($result, 201);

        return $request;
    }

    /**
     * @param $request
     *
     * @return array
     * @depends testCreateContactGroup
     */
    public function testGetContactGroup($request)
    {
        $this->client->request(
            'GET',
            $this->getUrl('oro_api_get_contactgroups')
        );
        $result = $this->client->getResponse();
        $result = json_decode($result->getContent(), true);
        $flag   = 1;
        foreach ($result as $group) {
            if ($group['label'] == $request['contact_group']['label']) {
                $flag = 0;
                break;
            }
        }
        $this->assertEquals(0, $flag);

        $this->client->request(
            'GET',
            $this->getUrl('oro_api_get_contactgroup', array('id' => $group['id']))
        );
        $result = $this->client->getResponse();
        $this->assertJsonResponseStatusCodeEquals($result, 200);

        return $group;
    }

    /**
     * @param $group
     * @param $request
     *
     * @depends testGetContactGroup
     * @depends testCreateContactGroup
     */
    public function testUpdateContactGroup($group, $request)
    {
        $group['label'] .= "_Updated";
        $this->client->request(
            'PUT',
            $this->getUrl('oro_api_put_contactgroup', array('id' => $group['id'])),
            $request
        );
        $result = $this->client->getResponse();
        $this->assertEmptyResponseStatusCodeEquals($result, 204);

        $this->client->request(
            'GET',
            $this->getUrl('oro_api_get_contactgroup', array('id' => $group['id']))
        );
        $result = $this->client->getResponse();
        $this->assertJsonResponseStatusCodeEquals($result, 200);
        $result = json_decode($result->getContent(), true);
        $this->assertEquals($request['contact_group']['label'], $result['label'], 'ContactGroup does not updated');
    }

    /**
     * @param $group
     *
     * @depends testGetContactGroup
     */
    public function testDeleteContact($group)
    {
        $this->client->request(
            'DELETE',
            $this->getUrl('oro_api_delete_contactgroup', array('id' => $group['id']))
        );
        $result = $this->client->getResponse();
        $this->assertEmptyResponseStatusCodeEquals($result, 204);

        $this->client->request(
            'GET',
            $this->getUrl('oro_api_get_contactgroup', array('id' => $group['id']))
        );
        $result = $this->client->getResponse();
        $this->assertJsonResponseStatusCodeEquals($result, 404);
    }
}
