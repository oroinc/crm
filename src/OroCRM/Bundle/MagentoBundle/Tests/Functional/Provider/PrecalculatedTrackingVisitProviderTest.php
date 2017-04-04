<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Provider;

use OroCRM\Bundle\MagentoBundle\Provider\PrecalculatedTrackingVisitProvider;
use OroCRM\Bundle\MagentoBundle\Provider\TrackingVisitProvider;
use OroCRM\Bundle\MagentoBundle\Tests\Functional\DataFixtures\LoadTrackingWebsiteToMagentoChannel;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TrackingBundle\Tests\Functional\DataFixtures\LoadTrackingVisits;

/**
 * @dbIsolation
 */
class PrecalculatedTrackingVisitProviderTest extends WebTestCase
{
    /**
     * @var PrecalculatedTrackingVisitProvider
     */
    private $provider;

    /**
     * @var TrackingVisitProvider
     */
    private $originalProvider;

    protected function setUp()
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->loadFixtures([
            LoadTrackingVisits::class,
            LoadTrackingWebsiteToMagentoChannel::class
        ]);
        $this->provider = $this->getContainer()->get('orocrm_magento.provider.tracking_visit');
        $this->originalProvider = new TrackingVisitProvider(
            $this->getContainer()->get('doctrine'),
            $this->getContainer()->get('oro_security.acl_helper')
        );
    }

    public function testDecoration()
    {
        $this->assertInstanceOf(PrecalculatedTrackingVisitProvider::class, $this->provider);
    }

    public function testGetVisitedCount()
    {
        $timezone = $this->getTimezone();
        $from = new \DateTime('2012-01-11', $timezone);
        $to = new \DateTime('2013-01-13', $timezone);

        $original = $this->originalProvider->getVisitedCount($from, $to);
        $precalculated = $this->provider->getVisitedCount($from, $to);

        $this->assertEquals($original, $precalculated);
    }

    public function testGetDeeplyVisitedCount()
    {
        $timezone = $this->getTimezone();
        $from = new \DateTime('2012-01-11', $timezone);
        $to = new \DateTime('2013-01-13', $timezone);

        $original = $this->originalProvider->getDeeplyVisitedCount($from, $to);
        $precalculated = $this->provider->getDeeplyVisitedCount($from, $to);

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
