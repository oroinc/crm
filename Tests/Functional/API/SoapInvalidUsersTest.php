<?php

namespace Oro\Bundle\UserBundle\Tests\Functional\API;

use Ddeboer\DataImport\Tests\Reader\ExcelReaderTest;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Acme\Bundle\TestsBundle\Test\ToolsAPI;
use Acme\Bundle\TestsBundle\Test\Client;

/**
 * @outputBuffering enabled
 */
class SoapInvalidUsersTest extends WebTestCase
{

    const USER_NAME = 'user_wo_permissions';
    const USER_PASSWORD = 'no_key';

    protected $client = null;

    public function testInvalidKey()
    {
        $this->client = static::createClient(array(), ToolsAPI::generateWsseHeader(ToolsAPI::USER_NAME, self::USER_PASSWORD));
        try {
            $this->client->soap(
                "http://localhost/api/soap",
                array(
                    'location' => 'http://localhost/api/soap',
                    'soap_version' => SOAP_1_2
                )
            );
        } catch (\Exception $e) {
            $this->assertEquals('Unauthorized', $e->getMessage());
        }
    }

    public function testInvalidUser()
    {
        $this->client = static::createClient(array(), ToolsAPI::generateWsseHeader(self::USER_NAME, ToolsAPI::USER_PASSWORD));
        try {
            $this->client->soap(
                "http://localhost/api/soap",
                array(
                    'location' => 'http://localhost/api/soap',
                    'soap_version' => SOAP_1_2
                )
            );
        } catch (\Exception $e) {
            $this->assertEquals('Unauthorized', $e->getMessage());
        }
    }
}
