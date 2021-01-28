<?php

namespace Oro\Bundle\TestFrameworkCRMBundle\Tests\Performance;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpKernel\Kernel;

class LoadTest extends WebTestCase
{
    const MAX_PAGES = 1000;
    const MAX_PAGE_TESTS = 100;

    protected $resultData;
    protected $resultLimit;

    protected function setUp(): void
    {
        $this->initClient(array("debug" => false), $this->generateBasicAuthHeader());
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
        $this->resultLimit = PHPUNIT\LOAD\LIMIT ;
        $this->resultData = $container->averageTime;
    }

    protected function assertPostConditions(): void
    {
        $data = $this->getName() . ',' . date('d/m/y') .  ',' . $this->resultLimit. ',' . $this->resultData . "\n";
        file_put_contents(
            getcwd() . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . 'statistics.txt',
            $data,
            FILE_APPEND
        );
        $this->assertLessThan(floatval($this->resultLimit), floatval($this->resultData), $this->getName());
    }

    public function testPager()
    {
        $url = $this->getUrl('oro_datagrid_index', array('gridName' => 'contacts-grid'));
        $averageTime = 0.0;
        for ($i  = 1; $i <= self::MAX_PAGE_TESTS; $i++) {
            $page = rand(1, self::MAX_PAGES);
            $parameters = array(
                '_pager' => array('_page' => $page, '_per_page' => 10),
                '_sort_by' => array('first_name' => 'ASC', 'last_name' => 'ASC')
            );
            $s = microtime(true);
            $this->client->request('GET', $url, array('contacts-grid' => $parameters), array(), array(), null, false);
            $e = microtime(true);
            $averageTime += ($e - $s);
            if ($i % 10 == 0) {
                echo "\n>> {$i} : " . $averageTime / $i;
            }
        }
        echo "\n>> Average Time: " . $averageTime/self::MAX_PAGE_TESTS;
        echo "\n>> Memory usage: " . memory_get_peak_usage(true)/1024;

        //export result
        $this->resultLimit = PHPUNIT\PAGE\LIMIT;
        $this->resultData = $averageTime/self::MAX_PAGE_TESTS;
    }
}
