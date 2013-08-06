<?php

namespace Oro\Bundle\FlexibleEntityBundle\Tests\Performance;

use Oro\Bundle\TestFrameworkBundle\Test\ToolsAPI;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\DependencyInjection\Container;
use Oro\Bundle\TestFrameworkBundle\Test\Client;

class LoadTest extends WebTestCase
{
    const MAX_PAGES = 1000;
    const MAX_PAGE_TESTS = 100;

    protected $result_data;
    protected $result_limit;

    /** @var  Client */
    protected $client;

    public function setUp()
    {
        $this->client = static::createClient(array("debug"=>false), ToolsAPI::generateBasicHeader());
    }

    protected function tearDown()
    {
        unset($this->client);
    }

    public function testLoad()
    {
        /** @var Kernel $kernel */
        $kernel = $this->client->getKernel();

        /** @var Container $container */
        $container = $this->client->getContainer();

        /** @var Application $application */
        $application = new \Symfony\Bundle\FrameworkBundle\Console\Application($kernel);
        $application->setAutoExit(false);

        $options = array('command' => 'doctrine:fixtures:load');
        $options['--fixtures'] = __DIR__ . DIRECTORY_SEPARATOR . "DataFixtures" . DIRECTORY_SEPARATOR . "Accounts";
        $options['--env'] = "test";
        $options['--append'] = null;
        $options['--no-interaction'] = null;
        $options['--no-debug'] = null;
        $application->run(new \Symfony\Component\Console\Input\ArrayInput($options));
        $container = $this->client->getContainer();

        echo "\n Average Time: " . $container->averageTime;
        //export result
        $this->result_limit = PHPUNIT_LOAD_LIMIT ;
        $this->result_data = $container->averageTime;
    }

    protected function assertPostConditions()
    {
        $data = $this->getName() . ',' . date('d/m/y') .  ',' . $this->result_limit. ',' . $this->result_data . "\n";
        file_put_contents(
            getcwd() . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . 'statistics.txt',
            $data,
            FILE_APPEND
        );
        $this->assertLessThan(floatval($this->result_limit), floatval($this->result_data), $this->getName());
    }

    public function testPager()
    {   $url = $this->client->generate('orocrm_contact_index', array('_format' => 'json'));
        $averageTime = 0.0;
        for ($i  = 1; $i <= self::MAX_PAGE_TESTS; $i++) {
            $page = rand(1, self::MAX_PAGES);
            $parameters = array(
                'contacts[_pager][_page]' => $page,
                'contacts[_pager][_per_page]' => 10,
                'contacts[_sort_by][first_name]' => 'ASC',
                'contacts[_sort_by][last_name]' => 'ASC'
            );
            $s = microtime(true);
            $this->client->request('GET', $url, $parameters, array(), array(), null, false);
            $e = microtime(true);
            $averageTime += ($e - $s);
            if ($i % 10 == 0) {
                echo "\n>> {$i} : " . $averageTime / $i;
            }
        }
        echo "\n>> Average Time: " . $averageTime/self::MAX_PAGE_TESTS;

        //export result
        $this->result_limit = PHPUNIT_PAGE_LIMIT;
        $this->result_data = $averageTime/self::MAX_PAGE_TESTS;
    }
}
