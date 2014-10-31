<?php

namespace OroCRM\Bundle\TaskBundle\Tests\Functional\Controller\Api\Soap;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @outputBuffering enabled
 * @dbIsolation
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
        $this->initClient(array(), $this->generateWsseAuthHeader());
        $this->initSoapClient();
    }

    /**
     * @return integer
     */
    public function testCreate()
    {
        $result = $this->soapClient->createTask($this->task);
        $this->assertTrue((bool) $result, $this->soapClient->__getLastResponse());

        return $result;
    }

    /**
     * @depends testCreate
     */
    public function testCget()
    {
        $tasks = $this->soapClient->getTasks();
        $tasks = $this->valueToArray($tasks);
        $this->assertCount(1, $tasks);
    }

    /**
     * @param integer $id
     * @depends testCreate
     */
    public function testGet($id)
    {
        $task = $this->soapClient->getTask($id);
        $task = $this->valueToArray($task);
        $this->assertEquals($this->task['subject'], $task['subject']);
    }

    /**
     * @param integer $id
     * @depends testCreate
     */
    public function testUpdate($id)
    {
        $task =  array_merge($this->task, ['subject' => 'Updated subject']);

        $result = $this->soapClient->updateTask($id, $task);
        $this->assertTrue($result);

        $updatedTask = $this->soapClient->getTask($id);
        $updatedTask = $this->valueToArray($updatedTask);

        $this->assertEquals($task['subject'], $updatedTask['subject']);
    }

    /**
     * @param integer $id
     * @depends testCreate
     */
    public function testDelete($id)
    {
        $result = $this->soapClient->deleteTask($id);
        $this->assertTrue($result);

        $this->setExpectedException('\SoapFault', 'Record with ID "' . $id . '" can not be found');
        $this->soapClient->getTask($id);
    }
}
