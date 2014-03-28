<?php

namespace OroCRM\Bundle\TaskBundle\Tests\Functional\Controller\Api\Soap;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Test\ToolsAPI;
use Oro\Bundle\TestFrameworkBundle\Test\Client;

/**
 * @outputBuffering enabled
 * @db_isolation
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
        $this->client = static::createClient(array(), ToolsAPI::generateWsseHeader());
        $this->client->soap(
            "http://localhost/api/soap",
            array(
                'location' => 'http://localhost/api/soap',
                'soap_version' => SOAP_1_2
            )
        );
    }

    /**
     * @return integer
     */
    public function testCreate()
    {
        $result = $this->client->getSoap()->createTask($this->task);
        $this->assertTrue((bool) $result, $this->client->getSoap()->__getLastResponse());

        return $result;
    }

    /**
     * @depends testCreate
     */
    public function testCget()
    {
        $tasks = $this->client->getSoap()->getTasks();
        $tasks = ToolsAPI::classToArray($tasks);
        $this->assertCount(1, $tasks);
    }

    /**
     * @param integer $id
     * @depends testCreate
     */
    public function testGet($id)
    {
        $task = $this->client->getSoap()->getTask($id);
        $task = ToolsAPI::classToArray($task);
        $this->assertEquals($this->task['subject'], $task['subject']);
    }

    /**
     * @param integer $id
     * @depends testCreate
     */
    public function testUpdate($id)
    {
        $task =  array_merge($this->task, ['subject' => 'Updated subject']);

        $result = $this->client->getSoap()->updateTask($id, $task);
        $this->assertTrue($result);

        $updatedTask = $this->client->getSoap()->getTask($id);
        $updatedTask = ToolsAPI::classToArray($updatedTask);

        $this->assertEquals($task['subject'], $updatedTask['subject']);
    }

    /**
     * @param integer $id
     * @depends testCreate
     */
    public function testDelete($id)
    {
        $result = $this->client->getSoap()->deleteTask($id);
        $this->assertTrue($result);

        $this->setExpectedException('\SoapFault', 'Record with ID "' . $id . '" can not be found');
        $this->client->getSoap()->getTask($id);
    }
}
