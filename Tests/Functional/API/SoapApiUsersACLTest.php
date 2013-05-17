<?php

namespace Oro\Bundle\UserBundle\Tests\Functional\API;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Test\ToolsAPI;
use Oro\Bundle\TestFrameworkBundle\Test\Client;

/**
 * @outputBuffering enabled
 */
class SoapApiUsersACLTest extends WebTestCase
{

    const USER_NAME = 'user_wo_permissions';
    const USER_PASSWORD = 'user_api_key';

    const DEFAULT_USER_ID = '1';

    protected $clientSoap = null;
    protected static $hasLoaded = false;

    public function setUp()
    {
        $this->clientSoap = static::createClient(array(), ToolsAPI::generateWsseHeader(self::USER_NAME, self::USER_PASSWORD));
        if (!self::$hasLoaded) {
            //rebuild indexes before tests
            $kernel = $this->clientSoap->getKernel();
            $application = new \Symfony\Bundle\FrameworkBundle\Console\Application($kernel);
            $application->setAutoExit(false);
            $options = array('command' => 'oro:search:reindex');
            $options['--env'] = "test";
            $options['--quiet'] = null;
            $application->run(new \Symfony\Component\Console\Input\ArrayInput($options));

            $this->clientSoap->startTransaction();
            $this->clientSoap->appendFixtures(__DIR__ . DIRECTORY_SEPARATOR . 'DataFixtures');
        }
        self::$hasLoaded = true;
    }

    public static function tearDownAfterClass()
    {
        Client::rollbackTransaction();
    }

    public function testWsseAccess()
    {
        try {
            $this->clientSoap->soap(
                "http://localhost/api/soap",
                array(
                    'location' => 'http://localhost/api/soap',
                    'soap_version' => SOAP_1_2
                )
            );
        } catch (\Exception $e) {
            $this->assertEquals('Forbidden', $e->getMessage());
        }
    }
}
