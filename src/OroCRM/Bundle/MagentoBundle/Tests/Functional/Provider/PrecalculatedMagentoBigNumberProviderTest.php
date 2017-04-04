<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Functional\Provider;

use OroCRM\Bundle\MagentoBundle\Tests\Functional\DataFixtures\LoadTrackingWebsiteToMagentoChannel;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TrackingBundle\Tests\Functional\DataFixtures\LoadTrackingVisits;
use OroCRM\Bundle\MagentoBundle\Provider\MagentoBigNumberProvider;
use OroCRM\Bundle\MagentoBundle\Provider\PrecalculatedMagentoBigNumberProvider;

/**
 * @dbIsolation
 */
class PrecalculatedMagentoBigNumberProviderTest extends WebTestCase
{
    /**
     * @var PrecalculatedMagentoBigNumberProvider
     */
    private $provider;

    /**
     * @var MagentoBigNumberProvider
     */
    private $originalProvider;

    protected function setUp()
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->loadFixtures([
            LoadTrackingVisits::class,
            LoadTrackingWebsiteToMagentoChannel::class
        ]);
        $this->provider = $this->getContainer()->get('orocrm_magento.provider.big_number');
        $this->originalProvider = new MagentoBigNumberProvider(
            $this->getContainer()->get('doctrine'),
            $this->getContainer()->get('oro_security.acl_helper'),
            $this->getContainer()->get('oro_dashboard.provider.big_number.date_helper')
        );
    }

    public function testDecoration()
    {
        $this->assertInstanceOf(PrecalculatedMagentoBigNumberProvider::class, $this->provider);
    }

    public function testGetVisitedCount()
    {
        $timezone = $this->getTimezone();
        $from = new \DateTime('2012-01-11', $timezone);
        $to = new \DateTime('2013-01-13', $timezone);
        $dateRange = ['start' => $from, 'end' => $to];

        $original = $this->originalProvider->getSiteVisitsValues($dateRange);
        $precalculated = $this->provider->getSiteVisitsValues($dateRange);

        $this->assertEquals($original, $precalculated);
    }

    /**
     * @return \DateTimeZone
     */
    private function getTimezone()
    {
        $configManager = $this->getContainer()->get('oro_config.global');

        $timezoneName = $configManager->get('oro_locale.timezone');
        if (!$timezoneName) {
            $timezoneName = 'UTC';
        }
        return new \DateTimeZone($timezoneName);
    }
}
