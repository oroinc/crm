<?php

namespace OroCRM\Bundle\TaskBundle\Tests\Functional\Controller\Api\Rest;

use orocrm\Bundle\TaskBundle\Entity\Task;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Test\ToolsAPI;
use Oro\Bundle\TestFrameworkBundle\Test\Client;

/**
 * @outputBuffering enabled
 * @db_isolation
 */
class TaskControllerACLTest extends WebTestCase
{
    const USER_NAME = 'user_wo_permissions';
    const USER_PASSWORD = 'user_api_key';

    /** @var Client */
    protected $client;

    /** @var Task */
    protected static $taskId;

    protected static $hasLoaded = false;

    public function setUp()
    {
        $this->client = static::createClient(
            [],
            ToolsAPI::generateWsseHeader(self::USER_NAME, self::USER_PASSWORD)
        );

        if (!self::$hasLoaded) {
            $this->client->appendFixtures(__DIR__ . DIRECTORY_SEPARATOR . 'DataFixtures');
            self::$taskId = $this->client->getContainer()
                ->get('doctrine')
                ->getManager()
                ->getRepository('OroCRMTaskBundle:Task')
                ->findOneBySubject('Acl task')
                ->getId();
        }
        self::$hasLoaded = true;
    }

    public function testCreate()
    {
        $request = [
            'task' => [
                'subject' => 'New task',
                'description' => 'New description',
                'dueDate' => '2014-03-04T20:00:00+0000',
                'taskPriority' => 'high',
                'owner' => '1',
                'reporter' => '1'
            ]
        ];

        $this->client->request(
            'POST',
            $this->client->generate('orocrm_api_post_task'),
            $request,
            [],
            ToolsAPI::generateWsseHeader(self::USER_NAME, self::USER_PASSWORD)
        );
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 403);
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
            ToolsAPI::generateWsseHeader(self::USER_NAME, self::USER_PASSWORD)
        );
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 403);
    }

    /**
     * @depends testCreate
     */
    public function testGet()
    {
        $this->client->request(
            'GET',
            $this->client->generate('orocrm_api_get_task', ['id' => self::$taskId]),
            [],
            [],
            ToolsAPI::generateWsseHeader(self::USER_NAME, self::USER_PASSWORD)
        );
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 403);
    }

    /**
     * @depends testCreate
     */
    public function testPut()
    {
        $updatedTask = ['subject' => 'Updated subject'];
        $this->client->request(
            'PUT',
            $this->client->generate('orocrm_api_put_task', ['id' => self::$taskId]),
            ['task' => $updatedTask],
            [],
            ToolsAPI::generateWsseHeader(self::USER_NAME, self::USER_PASSWORD)
        );
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 403);
    }

    /**
     * @depends testCreate
     */
    public function testDelete()
    {
        $this->client->request(
            'DELETE',
            $this->client->generate('orocrm_api_delete_task', ['id' => self::$taskId]),
            [],
            [],
            ToolsAPI::generateWsseHeader(self::USER_NAME, self::USER_PASSWORD)
        );
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 403);
    }
}
