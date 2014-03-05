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
class RestTaskACLTest extends WebTestCase
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
            array(),
            ToolsAPI::generateWsseHeader(self::USER_NAME, self::USER_PASSWORD)
        );

        if (!self::$hasLoaded) {
            $this->client->appendFixtures(__DIR__ . DIRECTORY_SEPARATOR . 'DataFixtures');
            self::$taskId = $this->client->getContainer()
                ->get('doctrine')
                ->getManager()
                ->getRepository('OroCRMTaskBundle:Task')
                ->findOneBySubject('New task')
                ->getId();
        }
        self::$hasLoaded = true;
    }

    public function testCreate()
    {
        $request = array(
            'task' => array(
                'subject' => 'New task',
                'description' => 'New description',
                'dueDate' => '2014-03-04T20:00:00+0000',
                'taskPriority' => 'high',
                'assignedTo' => '1',
                'owner' => '1'
            )
        );

        $this->client->request('POST', $this->client->generate('oro_api_post_task'), $request);
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 403);
    }

    /**
     * @depends testCreate
     */
    public function testCget()
    {
        $this->client->request('GET', $this->client->generate('oro_api_get_tasks'));
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
            $this->client->generate('oro_api_get_task', array('id' => self::$taskId))
        );
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 403);
    }

    /**
     * @depends testCreate
     */
    public function testPut()
    {
        $updatedTask = array('subject' => 'Updated title');
        $this->client->request(
            'PUT',
            $this->client->generate('oro_api_put_task', array('id' => self::$taskId)),
            array('task' => $updatedTask)
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
            $this->client->generate('oro_api_delete_task', array('id' => self::$taskId))
        );
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 403);
    }
}
