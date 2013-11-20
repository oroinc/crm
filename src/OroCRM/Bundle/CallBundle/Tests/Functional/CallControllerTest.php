<?php

namespace OroCRM\Bundle\CallBundle\Tests\Controller;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Test\ToolsAPI;
use Oro\Bundle\TestFrameworkBundle\Test\Client;

/**
 * @outputBuffering enabled
 * @db_isolation
 */
class CallControllerTest extends WebTestCase
{
    /**
     * @var Client
     */
    protected $client;

    public function setUp()
    {
        $this->client = static::createClient(array(), ToolsAPI::generateBasicHeader());
        $this->client = static::createClient(array(), array_merge(ToolsAPI::generateBasicHeader(), array('HTTP_X-CSRF-Header' => 1)));
    }
    
    public function testIndex()
    {
        $this->client->request('GET', $this->client->generate('orocrm_call_index'));
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200, 'text/html; charset=UTF-8');
    }
}
