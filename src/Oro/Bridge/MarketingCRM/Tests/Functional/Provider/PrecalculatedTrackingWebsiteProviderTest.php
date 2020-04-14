<?php

namespace Oro\Bridge\MarketingCRM\Tests\Functional\Provider;

use Oro\Bridge\MarketingCRM\Provider\PrecalculatedWebsiteVisitProvider;
use Oro\Bridge\MarketingCRM\Provider\WebsiteVisitProvider;
use Oro\Bridge\MarketingCRM\Tests\Functional\DataFixtures\LoadTrackingWebsiteToMagentoChannel;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TrackingBundle\Tests\Functional\DataFixtures\LoadTrackingVisits;

class PrecalculatedTrackingWebsiteProviderTest extends WebTestCase
{
    /**
     * @var PrecalculatedWebsiteVisitProvider
     */
    private $provider;

    /**
     * @var WebsiteVisitProvider
     */
    private $originalProvider;

    protected function setUp(): void
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->loadFixtures([
            LoadTrackingVisits::class,
            LoadTrackingWebsiteToMagentoChannel::class
        ]);
        $this->provider = $this->getContainer()->get('oro_magento.provider.website_visit');
        $this->originalProvider = new WebsiteVisitProvider(
            $this->getContainer()->get('doctrine'),
            $this->getContainer()->get('oro_security.acl_helper'),
            $this->getContainer()->get('oro_dashboard.provider.big_number.date_helper')
        );
    }

    public function testDecoration()
    {
        $this->assertInstanceOf(PrecalculatedWebsiteVisitProvider::class, $this->provider);
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

    public function testGetVisitedCountForNullFromDate()
    {
        $timezone = $this->getTimezone();
        $from = null;
        $to = new \DateTime('2013-01-13', $timezone);
        $dateRange = ['start' => $from, 'end' => $to];
        $original = $this->originalProvider->getSiteVisitsValues($dateRange);
        $precalculated = $this->provider->getSiteVisitsValues($dateRange);
        $this->assertEquals($original, $precalculated);
    }

    public function testGetVisitedCountForNullToDate()
    {
        $timezone = $this->getTimezone();
        $from = new \DateTime('2012-01-11', $timezone);
        $to = null;
        $dateRange = ['start' => $from, 'end' => $to];
        $original = $this->originalProvider->getSiteVisitsValues($dateRange);
        $precalculated = $this->provider->getSiteVisitsValues($dateRange);
        $this->assertEquals($original, $precalculated);
    }

    public function testGetVisitedCountForNullDates()
    {
        $from = $to = null;
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
