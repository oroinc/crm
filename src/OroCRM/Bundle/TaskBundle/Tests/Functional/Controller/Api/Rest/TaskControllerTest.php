<?php

namespace OroCRM\Bundle\TaskBundle\Tests\Functional\Controller\Api\Rest;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Test\ToolsAPI;
use Oro\Bundle\TestFrameworkBundle\Test\Client;

/**
 * @outputBuffering enabled
 * @db_isolation
 * @db_reindex
 */
class TaskControllerTest extends WebTestCase
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var array
     */
    protected $task = [
        'subject' => 'New task',
        'description' => 'New description',
        'dueDate' => '2014-03-04T20:00:00+0000',
        'taskPriority' => 'high',
        'owner' => 1,
        'reporter' => 1
    ];

    public function setUp()
    {
        $this->client = static::createClient([], ToolsAPI::generateWsseHeader());
    }

    public function testCreate()
    {
        $request = [
            'task' => $this->task
        ];

        $this->client->request(
            'POST',
            $this->client->generate('orocrm_api_post_task'),
            $request
        );
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 201);

        $task = ToolsAPI::jsonToArray($result->getContent());

        return $task['id'];
    }

    /**
     * @depends testCreate
     */
    public function testCget()
    {
        $this->client->request(
            'GET',
            $this->client->generate('orocrm_api_get_tasks'),
            [],
            [],
            ToolsAPI::generateWsseHeader()
        );
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);

        $tasks = ToolsAPI::jsonToArray($result->getContent());
        $this->assertCount(1, $tasks);
    }

    /**
     * @depends testCreate
     * @param integer $id
     */
    public function testGet($id)
    {
        $this->client->request(
            'GET',
            $this->client->generate('orocrm_api_get_task', ['id' => $id]),
            [],
            [],
            ToolsAPI::generateWsseHeader()
        );
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);

        $task = ToolsAPI::jsonToArray($result->getContent());
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
            $this->client->generate('orocrm_api_put_task', ['id' => $id]),
            ['task' =>$updatedTask],
            [],
            ToolsAPI::generateWsseHeader()
        );
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 204);

        $this->client->request(
            'GET',
            $this->client->generate('orocrm_api_get_task', ['id' => $id])
        );
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);

        $task = ToolsAPI::jsonToArray($result->getContent());
        $this->assertEquals(
            'Updated subject',
            $task['subject']
        );

        $task = ToolsAPI::jsonToArray($result->getContent());
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
            $this->client->generate('orocrm_api_delete_task', ['id' => $id]),
            [],
            [],
            ToolsAPI::generateWsseHeader()
        );
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 204);
        $this->client->request(
            'GET',
            $this->client->generate('orocrm_api_get_task', ['id' => $id]),
            [],
            [],
            ToolsAPI::generateWsseHeader()
        );
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 404);
    }
}
