<?php

namespace Oro\Bridge\MarketingCRM\Tests\Unit\Provider;

use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bridge\MarketingCRM\Provider\PrecalculatedWebsiteVisitProvider;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureChecker;
use Oro\Bundle\MagentoBundle\Provider\WebsiteVisitProviderInterface;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class PrecalculatedWebsiteVisitProviderTest extends \PHPUnit\Framework\TestCase
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
     * @var PrecalculatedWebsiteVisitProvider
     */
    private $provider;

    /**
     * @var WebsiteVisitProviderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $visitProvider;

    protected function setUp(): void
    {
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->aclHelper = $this->createMock(AclHelper::class);
        $this->configManager = $this->createMock(ConfigManager::class);
        $this->featureChecker = $this->createMock(FeatureChecker::class);

        $this->provider = new PrecalculatedWebsiteVisitProvider(
            $this->registry,
            $this->configManager,
            $this->aclHelper
        );
        $this->provider->setFeatureChecker($this->featureChecker);
        $this->provider->addFeature('test');

        $this->visitProvider = $this->createMock(WebsiteVisitProviderInterface::class);
        $this->provider->setVisitProvider($this->visitProvider);
    }

    public function testDecoration()
    {
        $this->assertInstanceOf(PrecalculatedWebsiteVisitProvider::class, $this->provider);
    }

    public function testGetDeeplyVisitedCountPrecalculationDisabled()
    {
        $from = new \DateTime();
        $to = new \DateTime();
        $dateRange = ['start' => $from, 'end' => $to];

        $this->isPrecalculationEnabled(false);
        $this->visitProvider->expects($this->once())
            ->method('getSiteVisitsValues')
            ->with($dateRange)
            ->willReturn(42);
        $this->assertSame(42, $this->provider->getSiteVisitsValues($dateRange));
    }

    public function testGetDeeplyVisitedCountFeatureDisabled()
    {
        $from = new \DateTime();
        $to = new \DateTime();
        $dateRange = ['start' => $from, 'end' => $to];

        $this->isPrecalculationEnabled(true);
        $this->visitProvider->expects($this->never())
            ->method($this->anything());

        $this->featureChecker->expects($this->once())
            ->method('isFeatureEnabled')
            ->with('test')
            ->willReturn(false);
        $this->assertSame(0, $this->provider->getSiteVisitsValues($dateRange));
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
