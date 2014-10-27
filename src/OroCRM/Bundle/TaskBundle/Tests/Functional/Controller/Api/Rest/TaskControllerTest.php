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
    /**
     * @var array
     */
    protected $task = [
        'subject' => 'New task',
        'description' => 'New description',
        'dueDate' => '2014-03-04T20:00:00+0000',
        'taskPriority' => 'high',
        'owner' => 1,
    ];

    protected function setUp()
    {
        $this->initClient([], $this->generateWsseAuthHeader());
    }

    public function testCreate()
    {
        $request = $this->task;

        $this->client->request(
            'POST',
            $this->getUrl('orocrm_api_post_task'),
            $request
        );

        $task = $this->getJsonResponseContent($this->client->getResponse(), 201);

        return $task['id'];
    }

    /**
     * @depends testCreate
     */
    public function testCget()
    {
        $this->client->request(
            'GET',
            $this->getUrl('orocrm_api_get_tasks'),
            [],
            [],
            $this->generateWsseAuthHeader()
        );

        $tasks = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertCount(1, $tasks);
    }

    /**
     * @depends testCreate
     */
    public function testCgetFiltering()
    {
        $this->client->request(
            'GET',
            $this->getUrl('orocrm_api_get_tasks') . '?createdAt>2014-03-04T20:00:00+0000',
            [],
            [],
            $this->generateWsseAuthHeader()
        );

        $tasks = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertCount(1, $tasks);

        $this->client->request(
            'GET',
            $this->getUrl('orocrm_api_get_tasks') . '?createdAt>2050-03-04T20:00:00+0000',
            [],
            [],
            $this->generateWsseAuthHeader()
        );

        $entities = $this->getJsonResponseContent($this->client->getResponse(), 200);
        $this->assertEmpty($entities);
    }

    /**
     * @depends testCreate
     * @param integer $id
     */
    public function testGet($id)
    {
        $this->client->request(
            'GET',
            $this->getUrl('orocrm_api_get_task', ['id' => $id]),
            [],
            [],
            $this->generateWsseAuthHeader()
        );

        $task = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertEquals($this->task['subject'], $task['subject']);
    }

    /**
     * @depends testCreate
     * @param integer $id
     */
    public function testPut($id)
    {
        $updatedTask =  array_merge($this->task, ['subject' => 'Updated subject']);
        $this->client->request(
            'PUT',
            $this->getUrl('orocrm_api_put_task', ['id' => $id]),
            $updatedTask,
            [],
            $this->generateWsseAuthHeader()
        );
        $result = $this->client->getResponse();
        $this->assertEmptyResponseStatusCodeEquals($result, 204);

        $this->client->request(
            'GET',
            $this->getUrl('orocrm_api_get_task', ['id' => $id])
        );

        $task = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertEquals(
            'Updated subject',
            $task['subject']
        );

        $this->assertEquals($updatedTask['subject'], $task['subject']);
    }

    /**
     * @depends testCreate
     * @param integer $id
     */
    public function testDelete($id)
    {
        $this->client->request(
            'DELETE',
            $this->getUrl('orocrm_api_delete_task', ['id' => $id]),
            [],
            [],
            $this->generateWsseAuthHeader()
        );
        $result = $this->client->getResponse();
        $this->assertEmptyResponseStatusCodeEquals($result, 204);
        $this->client->request(
            'GET',
            $this->getUrl('orocrm_api_get_task', ['id' => $id]),
            [],
            [],
            $this->generateWsseAuthHeader()
        );
        $result = $this->client->getResponse();
        $this->assertJsonResponseStatusCodeEquals($result, 404);
    }
}
