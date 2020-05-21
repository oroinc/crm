<?php

namespace Oro\Bridge\MarketingCRM\Tests\Unit\Provider;

use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bridge\MarketingCRM\Provider\PrecalculatedTrackingVisitProvider;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureChecker;
use Oro\Bundle\MagentoBundle\Provider\TrackingVisitProviderInterface;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class PrecalculatedTrackingVisitProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject
     */
    private $registry;

    /**
     * @var AclHelper|\PHPUnit\Framework\MockObject\MockObject
     */
    private $aclHelper;

    /**
     * @var ConfigManager|\PHPUnit\Framework\MockObject\MockObject
     */
    private $configManager;

    /**
     * @var FeatureChecker|\PHPUnit\Framework\MockObject\MockObject
     */
    private $featureChecker;

    /**
     * @var PrecalculatedTrackingVisitProvider
     */
    private $provider;

    /**
     * @var TrackingVisitProviderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $visitProvider;

    protected function setUp(): void
    {
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->aclHelper = $this->createMock(AclHelper::class);
        $this->configManager = $this->createMock(ConfigManager::class);
        $this->featureChecker = $this->createMock(FeatureChecker::class);

        $this->provider = new PrecalculatedTrackingVisitProvider(
            $this->registry,
            $this->configManager,
            $this->aclHelper
        );
        $this->provider->setFeatureChecker($this->featureChecker);
        $this->provider->addFeature('test');

        $this->visitProvider = $this->createMock(TrackingVisitProviderInterface::class);
        $this->provider->setVisitProvider($this->visitProvider);
    }

    public function testGetAggregates()
    {
        $customers = [];

        $this->visitProvider->expects($this->once())
            ->method('getAggregates')
            ->with($customers);

        $this->provider->getAggregates($customers);
    }

    public function testGetDeeplyVisitedCountPrecalculationDisabled()
    {
        $from = new \DateTime();
        $to = new \DateTime();

        $this->isPrecalculationEnabled(false);
        $this->visitProvider->expects($this->once())
            ->method('getDeeplyVisitedCount')
            ->with($from, $to)
            ->willReturn(42);
        $this->assertSame(42, $this->provider->getDeeplyVisitedCount($from, $to));
    }

    public function testGetDeeplyVisitedCountFeatureDisabled()
    {
        $from = new \DateTime();
        $to = new \DateTime();

        $this->isPrecalculationEnabled(true);
        $this->visitProvider->expects($this->never())
            ->method($this->anything());

        $this->featureChecker->expects($this->once())
            ->method('isFeatureEnabled')
            ->with('test')
            ->willReturn(false);
        $this->assertSame(0, $this->provider->getDeeplyVisitedCount($from, $to));
    }

    public function testGetVisitedCountPrecalculationDisabled()
    {
        $from = new \DateTime();
        $to = new \DateTime();

        $this->isPrecalculationEnabled(false);
        $this->visitProvider->expects($this->once())
            ->method('getVisitedCount')
            ->with($from, $to)
            ->willReturn(42);
        $this->assertSame(42, $this->provider->getVisitedCount($from, $to));
    }

    public function testGetVisitedCountFeatureDisabled()
    {
        $from = new \DateTime();
        $to = new \DateTime();

        $this->isPrecalculationEnabled(true);
        $this->visitProvider->expects($this->never())
            ->method($this->anything());

        $this->featureChecker->expects($this->once())
            ->method('isFeatureEnabled')
            ->with('test')
            ->willReturn(false);
        $this->assertSame(0, $this->provider->getVisitedCount($from, $to));
    }

    /**
     * @param bool $enabled
     */
    private function isPrecalculationEnabled($enabled)
    {
        $this->configManager->expects($this->once())
            ->method('get')
            ->with('oro_tracking.precalculated_statistic_enabled')
            ->willReturn($enabled);
    }
}
