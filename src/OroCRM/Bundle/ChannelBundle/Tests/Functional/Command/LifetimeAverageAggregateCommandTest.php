<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Functional\Command;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use OroCRM\Bundle\ChannelBundle\Entity\Repository\LifetimeValueAverageAggregationRepository;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class LifetimeAverageAggregateCommandTest extends WebTestCase
{
    const TEST_START_DATE = '2014-01-01 00:00:00';

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
                '$expectedContent' => "Usage:\n oro:cron:lifetime-average:aggregate [-f|--force]",
                '$params'          => ['--help']
            ],
            'should show success output'                          => [
                '$expectedContent' => 'Completed!',
                '$params'          => []
            ],
            'should show success output and info about force run' => [
                '$expectedContent' => "Removing existing data...\nCompleted!",
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
        $cm = $this->getContainer()->get('oro_config.global');
        $cm->set('oro_locale.timezone', $systemTimezone);
        $cm->flush();

        $this->runCommand('oro:cron:lifetime-average:aggregate', ['-f' => true, '--use-delete' => true]);

        /** @var LifetimeValueAverageAggregationRepository $repo */
        $repo = $this->getContainer()
            ->get('doctrine')
            ->getRepository('OroCRMChannelBundle:LifetimeValueAverageAggregation');

        $values = $repo->findAmountStatisticsByDate(self::TEST_START_DATE);
        $this->assertSame([], $values);
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
}
