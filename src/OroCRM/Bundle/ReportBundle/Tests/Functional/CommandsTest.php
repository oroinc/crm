<?php

namespace OroCRM\Bundle\ReportBundle\Tests\Functional;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Test\Client;

use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\Kernel;

use Symfony\Component\Console\Application;
use OroCRM\Bundle\ReportBundle\Command\ReportUpdateCommand;

/**
 * @outputBuffering enabled
 * @db_isolation
 * @db_reindex
 */
class CommandsTest extends WebTestCase
{
    static protected $fixturesLoaded = false;

    /**
     * @var Client
     */
    protected $client;

    public function setUp()
    {
        $this->client = static::createClient();
        if (!self::$fixturesLoaded) {
            $this->client->appendFixtures(__DIR__ . DIRECTORY_SEPARATOR . 'DataFixtures', array('LoadLead'));
            self::$fixturesLoaded = true;
        }
    }

    public function testReportUpdate()
    {
        /** @var Kernel $kernel */
        $kernel = $this->client->getKernel();

        /** @var Application $application */
        $application = new \Symfony\Bundle\FrameworkBundle\Console\Application($kernel);
        $application->setAutoExit(false);

        $command = new ReportUpdateCommand();
        $command->setApplication($application);
        $commandTester = new CommandTester($command);
        $commandTester->execute(array('command' => $command->getName(), '--env' => $kernel->getEnvironment()));

        $this->assertEquals(
            "Update report transactional tables" . PHP_EOL ."Completed" . PHP_EOL,
            $commandTester->getDisplay()
        );
    }
}
