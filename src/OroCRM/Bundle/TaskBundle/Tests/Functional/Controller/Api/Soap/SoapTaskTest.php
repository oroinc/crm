<?php

namespace OroCRM\Bundle\TaskBundle\Tests\Functional\API;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Test\ToolsAPI;
use Oro\Bundle\TestFrameworkBundle\Test\Client;

/**
 * @outputBuffering enabled
 * @db_isolation
 */
class SoapTaskTest extends WebTestCase
{
    /** @var Client */
    protected $client;

    public function setUp()
    {
        $this->markTestSkipped();

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
     * @return array
     */
    public function testCreate()
    {
        $request = array (
            "name" => 'Task_name_' . mt_rand(),
            "owner" => '1',
        );

        $result = $this->client->getSoap()->createTask($request);
        $this->assertTrue((bool) $result, $this->client->getSoap()->__getLastResponse());

        $request['id'] = $result;
        return $request;
    }

    /**
     * @param $request
     * @depends testCreate
     * @return array
     */
    public function testGet($request)
    {
        $tasks = $this->client->getSoap()->getTasks(1, 1000);
        $tasks = ToolsAPI::classToArray($tasks);
        $taskName = $request['name'];
        $task = $tasks['item'];
        if (isset($task[0])) {
            $task = array_filter(
                $task,
                function ($a) use ($taskName) {
                    return $a['name'] == $taskName;
                }
            );
            $task = reset($task);
        }

        $this->assertEquals($request['name'], $task['name']);
        $this->assertEquals($request['id'], $task['id']);
    }

    /**
     * @param $request
     * @depends testCreate
     */
    public function testUpdate($request)
    {
        $taskUpdate = $request;
        unset($taskUpdate['id']);
        $taskUpdate['name'] .= '_Updated';

        $result = $this->client->getSoap()->updateTask($request['id'], $taskUpdate);
        $this->assertTrue($result);

        $task = $this->client->getSoap()->getTask($request['id']);
        $task = ToolsAPI::classToArray($task);

        $this->assertEquals($taskUpdate['name'], $task['name']);

        return $request;
    }

    /**
     * @param $request
     * @depends testUpdate
     */
    public function testDelete($request)
    {
        $result = $this->client->getSoap()->deleteTask($request['id']);
        $this->assertTrue($result);

        $this->setExpectedException('\SoapFault', 'Record with ID "' . $request['id'] . '" can not be found');
        $this->client->getSoap()->getTask($request['id']);
    }
}
