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
    /** @var Client */
    protected $client;

    public function setUp()
    {
        $this->client = static::createClient([], ToolsAPI::generateWsseHeader());
    }

    public function testCreate()
    {
        $this->markTestSkipped();

        $request = [
            'task' => [
                'subject' => 'New task',
                'description' => 'New description',
                'dueDate' => '2014-03-04T20:00:00+0000',
                'taskPriority' => 'high',
                'assignedTo' => '1',
                'owner' => '1'
            ]
        ];

        $this->client->request(
            'POST',
            $this->client->generate('orocrm_api_post_task'),
            $request
        );
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 201);

        $task = json_decode($result->getContent(), true);
        $this->assertEquals($task['subject'], $request['task']['subject']);
    }

    /**
     * @depends testCreate
     * @return array
     */
    public function testCget()
    {
        $this->markTestSkipped();

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

        return reset($tasks);
    }

    /**
     * @depends testCget
     * @param array $expectedTask
     */
    public function testGet($expectedTask)
    {
        $this->client->request(
            'GET',
            $this->client->generate('orocrm_api_get_task', ['id' => $expectedTask['id']])
            [],
            [],
            ToolsAPI::generateWsseHeader()
        );
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);

        $task = json_decode($result->getContent(), true);
        $this->assertEquals($expectedTask, $task);
    }

    /**
     * @depends testCget
     * @param array $task
     */
    public function testPut($task)
    {
        $updatedTask = ['subject' => 'Updated subject'];
        $this->client->request(
            'PUT',
            $this->client->generate('orocrm_api_put_task', ['id' => $task['id']]),
            ['task' => $updatedTask],
            [],
            ToolsAPI::generateWsseHeader()
        );
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);

        $task = json_decode($result->getContent(), true);
        $this->assertEquals($task['subject'], $updatedTask['subject']);
    }

    /**
     * @depends testCget
     * @param array $task
     */
    public function testDelete($task)
    {
        $this->client->request(
            'DELETE',
            $this->client->generate('orocrm_api_delete_task', ['id' => $task['id']]),
            [],
            [],
            ToolsAPI::generateWsseHeader()
        );
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 204);
        $this->client->request(
            'GET',
            $this->client->generate('orocrm_api_get_task', ['id' => $task['id']]),
            [],
            [],
            ToolsAPI::generateWsseHeader()
        );
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 404);
    }
}
