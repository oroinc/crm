<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Functional\Command;

use Symfony\Component\Yaml\Yaml;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use OroCRM\Bundle\ChannelBundle\Entity\Repository\LifetimeValueAverageAggregationRepository;

/**
 * @outputBuffering false
 * @dbIsolation
 */
class LifetimeAverageAggregateCommandTest extends WebTestCase
{
    const TEST_START_DATE = '2013-10-01 00:00:00';

    /** @var array */
    protected $channelMap;

    protected function setUp()
    {
        $this->initClient();
        $this->loadFixtures(['OroCRM\Bundle\ChannelBundle\Tests\Functional\Fixture\LoadLifetimeHistoryData']);
    }

    /**
     * @dataProvider paramProvider
     *
     * @param string $expectedContent
     * @param array  $params
     */
    public function testCommandOutput($expectedContent, $params)
    {
        $result = $this->runCommand('oro:cron:lifetime-average:aggregate', $params);
        $this->assertContains($expectedContent, $result);
    }

    /**
     * @return array
     */
    public function paramProvider()
    {
        return [
            'should show help'                                    => [
                '$expectedContent' => "Usage:\n  oro:cron:lifetime-average:aggregate [options]",
                '$params'          => ['--help']
            ],
            'should show success output'                          => [
                '$expectedContent' => 'Completed!',
                '$params'          => []
            ],
            'should show success output and info about force run' => [
                '$expectedContent' => "Removing existing data..." . PHP_EOL . "Completed!",
                '$params'          => ['-f' => true, '--use-delete' => true]
            ]
        ];
    }

    /**
     * @dataProvider timezoneProvider
     *
     * @param string $systemTimezone
     */
    public function testValuesAggregation($systemTimezone)
    {
        $this->getContainer()->set('oro_locale.settings', null);
        $cm = $this->getContainer()->get('oro_config.global');
        $cm->set('oro_locale.timezone', $systemTimezone);
        $cm->flush();

        $this->runCommand('oro:cron:lifetime-average:aggregate', ['-f' => true, '--use-delete' => true]);

        /** @var LifetimeValueAverageAggregationRepository $repo */
        $repo = $this->getContainer()
            ->get('doctrine')
            ->getRepository('OroCRMChannelBundle:LifetimeValueAverageAggregation');

        $fileName                = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Fixture'
            . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'expected_results.yml';
        $expectedResults         = Yaml::parse(file_get_contents($fileName));
        $expectedTimeZoneResults = $expectedResults['data'][$systemTimezone];
        $channelMap              = $this->getChannelIdMap();

        $values = $repo->findForPeriod(new \DateTime(self::TEST_START_DATE, new \DateTimeZone('UTC')));
        foreach ($values as $channelMonthData) {
            $key         = sprintf('%02d_%d', $channelMonthData['month'], $channelMonthData['year']);
            $channelName = $channelMap[$channelMonthData['channelId']];
            if (isset($expectedTimeZoneResults[$channelName], $expectedTimeZoneResults[$channelName][$key])) {
                $this->assertEquals(
                    $expectedTimeZoneResults[$channelName][$key],
                    $channelMonthData['amount'],
                    sprintf('Not equals for channel "%s" and month "%s"', $channelName, $key)
                );
            }
        }
    }

    /**
     * @return array
     */
    public function timezoneProvider()
    {
        return [
            'UTC'         => ['$systemTimezone' => 'UTC'],
            'Kiev'        => ['$systemTimezone' => 'Europe/Kiev'],
            'Los angeles' => ['$systemTimezone' => 'America/Los_Angeles'],
        ];
    }

    /**
     * @return array
     */
    protected function getChannelIdMap()
    {
        if (null === $this->channelMap) {
            $items = $this->getContainer()->get('doctrine')
                ->getRepository('OroCRMChannelBundle:Channel')
                ->createQueryBuilder('c')
                ->select('c.id, c.name')
                ->getQuery()
                ->getArrayResult();

            $this->channelMap = [];
            foreach ($items as $item) {
                $this->channelMap[$item['id']] = $item['name'];
            }
        }

        return $this->channelMap;
    }
}
