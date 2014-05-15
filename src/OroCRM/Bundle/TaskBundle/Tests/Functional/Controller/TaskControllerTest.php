<?php

namespace OroCRM\Bundle\TaskBundle\Tests\Functional\Controller;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @outputBuffering enabled
 * @dbIsolation
 * @dbReindex
 */
class TaskControllersTest extends WebTestCase
{
    public function setUp()
    {
        $this->initClient(array(), $this->generateBasicAuthHeader());
    }

    public function testCreate()
    {
        $crawler = $this->client->request('GET', $this->getUrl('orocrm_task_create'));
        $form = $crawler->selectButton('Save and Close')->form();
        $form['orocrm_task[subject]'] = 'New task';
        $form['orocrm_task[description]'] = 'New description';
        $form['orocrm_task[dueDate]'] = '2014-03-04T20:00:00+0000';
        $form['orocrm_task[owner]'] = '1';
        $form['orocrm_task[reporter]'] = '1';

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains("Task saved", $crawler->html());
    }

    /**
     * @depends testCreate
     */
    public function testUpdate()
    {
        $response = $this->client->requestGrid(
            'tasks-grid',
            array('tasks-grid[_filter][reporterName][value]' => 'John Doe')
        );

        $result = $this->getJsonResponseContent($response, 200);
        $result = reset($result['data']);

        $crawler = $this->client->request(
            'GET',
            $this->getUrl('orocrm_task_update', array('id' => $result['id']))
        );

        $form = $crawler->selectButton('Save and Close')->form();
        $form['orocrm_task[subject]'] = 'Task updated';
        $form['orocrm_task[description]'] = 'Description updated';

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains("Task saved", $crawler->html());
    }

    /**
     * @depends testUpdate
     */
    public function testView()
    {
        $response = $this->client->requestGrid(
            'tasks-grid',
            array('tasks-grid[_filter][reporterName][value]' => 'John Doe')
        );

        $result = $this->getJsonResponseContent($response, 200);
        $result = reset($result['data']);

        $this->client->request(
            'GET',
            $this->getUrl('orocrm_task_view', array('id' => $result['id']))
        );

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains('Task updated - Tasks - Activities', $result->getContent());
    }

    /**
     * @depends testUpdate
     */
    public function testIndex()
    {
        $this->client->request('GET', $this->getUrl('orocrm_task_index'));
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains('Task updated', $result->getContent());
    }
}
