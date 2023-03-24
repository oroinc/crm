<?php

namespace Oro\Bundle\ChannelBundle\Tests\Functional\Async;

use Oro\Bundle\ChannelBundle\Async\AggregateLifetimeAverageProcessor;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Entity\LifetimeValueAverageAggregation;
use Oro\Bundle\ChannelBundle\Entity\Repository\LifetimeValueAverageAggregationRepository;
use Oro\Bundle\ConfigBundle\Tests\Functional\Traits\ConfigManagerAwareTestTrait;
use Oro\Bundle\MessageQueueBundle\Test\Functional\MessageQueueExtension;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * @dbIsolationPerTest
 */
class AggregateLifetimeAverageProcessorTest extends WebTestCase
{
    use ConfigManagerAwareTestTrait;
    use MessageQueueExtension;

    protected function setUp(): void
    {
        $this->initClient();
    }

    public function testCouldBeGetFromContainerAsService(): void
    {
        $processor = self::getContainer()->get('oro_channel.async.aggregate_lifetime_average_processor');

        self::assertInstanceOf(AggregateLifetimeAverageProcessor::class, $processor);
    }

    /**
     * @dataProvider timezoneProvider
     */
    public function testValuesAggregation(string $systemTimezone): void
    {
        $configManager = self::getConfigManager();
        $configManager->set('oro_locale.timezone', $systemTimezone);
        $configManager->flush();

        self::consume();
        self::clearMessageCollector();

        /** @var LifetimeValueAverageAggregationRepository $repository */
        $repository = self::getContainer()->get('doctrine')->getRepository(LifetimeValueAverageAggregation::class);

        $expectedTimeZoneResults = $this->getExpectedResultsFor($systemTimezone);
        $channelMap = $this->getChannelIdMap();

        $values = $repository->findForPeriod(new \DateTime('2013-10-01 00:00:00', new \DateTimeZone('UTC')));
        foreach ($values as $channelMonthData) {
            $key = sprintf('%02d_%d', $channelMonthData['month'], $channelMonthData['year']);
            $channelName = $channelMap[$channelMonthData['channelId']];
            if (isset($expectedTimeZoneResults[$channelName], $expectedTimeZoneResults[$channelName][$key])) {
                self::assertEquals(
                    $expectedTimeZoneResults[$channelName][$key],
                    $channelMonthData['amount'],
                    sprintf('Not equals for channel "%s" and month "%s"', $channelName, $key)
                );
            }
        }
    }

    public function timezoneProvider(): array
    {
        return [
            'UTC' => ['$systemTimezone' => 'UTC'],
            'Kiev' => ['$systemTimezone' => 'Europe/Kiev'],
            'Los angeles' => ['$systemTimezone' => 'America/Los_Angeles'],
        ];
    }

    private function getExpectedResultsFor(string $timeZone): array
    {
        $expectedResults = Yaml::parse(file_get_contents(__DIR__ .'/../Fixture/data/expected_results.yml'));

        return $expectedResults['data'][$timeZone];
    }

    private function getChannelIdMap(): array
    {
        $channelMap = [];

        $items = self::getContainer()->get('doctrine')
            ->getRepository(Channel::class)
            ->createQueryBuilder('c')
            ->select('c.id, c.name')
            ->getQuery()
            ->getArrayResult();

        foreach ($items as $item) {
            $channelMap[$item['id']] = $item['name'];
        }

        return $channelMap;
    }
}
