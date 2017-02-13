<?php
namespace Oro\Bundle\ChannelBundle\Tests\Functional\Async;

use Oro\Bundle\ChannelBundle\Async\AggregateLifetimeAverageProcessor;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Entity\LifetimeValueAverageAggregation;
use Oro\Bundle\ChannelBundle\Entity\Repository\LifetimeValueAverageAggregationRepository;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Component\MessageQueue\Transport\Null\NullMessage;
use Oro\Component\MessageQueue\Transport\Null\NullSession;
use Oro\Component\MessageQueue\Util\JSON;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * @dbIsolationPerTest
 */
class AggregateLifetimeAverageProcessorTest extends WebTestCase
{
    protected function setUp()
    {
        parent::setUp();
        
        $this->initClient();
    }
    
    public function testCouldBeGetFromContainerAsService()
    {
        $processor = $this->getContainer()->get('oro_channel.async.aggregate_lifetime_average_processor');
        
        $this->assertInstanceOf(AggregateLifetimeAverageProcessor::class, $processor);
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

        /** @var AggregateLifetimeAverageProcessor $processor */
        $processor = $this->getContainer()->get('oro_channel.async.aggregate_lifetime_average_processor');

        $message = new NullMessage();
        $message->setBody(JSON::encode(['force' => true, 'use_truncate' => false]));
        $message->setMessageId(uniqid('oro', true));

        $processor->process($message, new NullSession());

        /** @var LifetimeValueAverageAggregationRepository $repository */
        $repository = $this->getDoctrine()->getRepository(LifetimeValueAverageAggregation::class);

        $expectedTimeZoneResults = $this->getExpectedResultsFor($systemTimezone);
        $channelMap = $this->getChannelIdMap();

        $values = $repository->findForPeriod(new \DateTime('2013-10-01 00:00:00', new \DateTimeZone('UTC')));
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
     * @param string $timeZone
     *
     * @return array
     */
    private function getExpectedResultsFor($timeZone)
    {
        $expectedResults = Yaml::parse(file_get_contents(__DIR__ .'/../Fixture/data/expected_results.yml'));

        return $expectedResults['data'][$timeZone];
    }

    /**
     * @return RegistryInterface
     */
    private function getDoctrine()
    {
        return $this->getContainer()->get('doctrine');
    }

    /**
     * @return \string[]
     */
    private function getChannelIdMap()
    {
        $channelMap = [];

        $items = $this->getContainer()->get('doctrine')
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
