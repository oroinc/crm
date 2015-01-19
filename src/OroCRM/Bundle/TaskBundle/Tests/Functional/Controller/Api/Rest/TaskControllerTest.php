<?php

namespace OroCRM\Bundle\TaskBundle\Tests\Functional\Controller\Api\Rest;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @outputBuffering enabled
 * @dbIsolation
 * @dbReindex
 */
class TaskControllerTest extends WebTestCase
{
    /** @var array */
    protected $task = [
        'subject'      => 'New task',
        'description'  => 'New description',
        'dueDate'      => '2014-03-04T20:00:00+0000',
        'taskPriority' => 'high',
    ];

    protected function setUp()
    {
        $this->initClient([], $this->generateWsseAuthHeader());

        if (!isset($this->task['owner'])) {
            $this->task['owner'] = $this->getContainer()
                ->get('doctrine')
                ->getRepository('OroUserBundle:User')
                ->findOneBy(['username' => self::USER_NAME])->getId();
        }
    }

    public function testCreate()
    {
        $this->client->request('POST', $this->getUrl('orocrm_api_post_task'), $this->task);
        $task = $this->getJsonResponseContent($this->client->getResponse(), 201);

        return $task['id'];
    }

    /**
     * @depends testCreate
     */
    public function testCget()
    {
        $this->client->request('GET', $this->getUrl('orocrm_api_get_tasks'));
        $tasks = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertCount(1, $tasks);
    }

    /**
     * @depends testCreate
     */
    public function testCgetFiltering()
    {
        $baseUrl = $this->getUrl('orocrm_api_get_tasks');

        $date     = '2014-03-04T20:00:00+0000';
        $ownerId  = $this->task['owner'];
        $randomId = rand($ownerId + 1, $ownerId + 100);

        $this->client->request('GET', $baseUrl . '?createdAt>' . $date);
        $this->assertCount(1, $this->getJsonResponseContent($this->client->getResponse(), 200));

        $this->client->request('GET', $baseUrl . '?createdAt<' . $date);
        $this->assertEmpty($this->getJsonResponseContent($this->client->getResponse(), 200));

        $this->client->request('GET', $baseUrl . '?ownerId=' . $ownerId);
        $this->assertCount(1, $this->getJsonResponseContent($this->client->getResponse(), 200));

        $this->client->request('GET', $baseUrl . '?ownerId=' . $randomId);
        $this->assertEmpty($this->getJsonResponseContent($this->client->getResponse(), 200));

        $this->client->request('GET', $baseUrl . '?ownerUsername=' . self::USER_NAME);
        $this->assertCount(1, $this->getJsonResponseContent($this->client->getResponse(), 200));

        $this->client->request('GET', $baseUrl . '?ownerUsername<>' . self::USER_NAME);
        $this->assertEmpty($this->getJsonResponseContent($this->client->getResponse(), 200));
    }

    /**
     * @depends testCreate
     *
     * @param integer $id
     */
    public function testGet($id)
    {
        $this->client->request('GET', $this->getUrl('orocrm_api_get_task', ['id' => $id]));
        $task = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertEquals($this->task['subject'], $task['subject']);
    }

    /**
     * @depends testCreate
     *
     * @param integer $id
     */
    public function testPut($id)
    {
        $updatedTask = array_merge($this->task, ['subject' => 'Updated subject']);
        $this->client->request('PUT', $this->getUrl('orocrm_api_put_task', ['id' => $id]), $updatedTask);
        $result = $this->client->getResponse();
        $this->assertEmptyResponseStatusCodeEquals($result, 204);

        $this->client->request('GET', $this->getUrl('orocrm_api_get_task', ['id' => $id]));

        $task = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertEquals('Updated subject', $task['subject']);
        $this->assertEquals($updatedTask['subject'], $task['subject']);
    }

    /**
     * @depends testCreate
     *
     * @param integer $id
     */
    public function testDelete($id)
    {
        $this->client->request('DELETE', $this->getUrl('orocrm_api_delete_task', ['id' => $id]));
        $result = $this->client->getResponse();
        $this->assertEmptyResponseStatusCodeEquals($result, 204);

        $this->client->request('GET', $this->getUrl('orocrm_api_get_task', ['id' => $id]));
        $result = $this->client->getResponse();
        $this->assertJsonResponseStatusCodeEquals($result, 404);
    }
}
