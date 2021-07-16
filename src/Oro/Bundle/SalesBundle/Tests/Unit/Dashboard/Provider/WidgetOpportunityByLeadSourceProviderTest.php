<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Dashboard\Provider;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Oro\Bundle\CurrencyBundle\Query\CurrencyQueryBuilderTransformerInterface;
use Oro\Bundle\DashboardBundle\Filter\DateFilterProcessor;
use Oro\Bundle\EntityExtendBundle\Twig\EnumExtension;
use Oro\Bundle\SalesBundle\Dashboard\Provider\WidgetOpportunityByLeadSourceProvider;
use Oro\Bundle\SalesBundle\Entity\Repository\OpportunityRepository;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Symfony\Contracts\Translation\TranslatorInterface;

class WidgetOpportunityByLeadSourceProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider opportunitiesBySourceDataProvider
     */
    public function testSortByValue(array $inputData)
    {
        $provider = $this->getProvider($inputData);

        $data = $provider->getChartData([], []);

        $this->assertSame(['source' => 'affiliate', 'value' => 19], $data[1]);
        $this->assertSame(['source' => 'direct_mail', 'value' => 15], $data[2]);
        $this->assertSame(['source' => 'website', 'value' => 12], $data[3]);
    }

    /**
     * @dataProvider opportunitiesBySourceDataProvider
     */
    public function testCreateOthersCategoryWithExcludedSources(array $inputData)
    {
        $provider = $this->getProvider($inputData);
        $data = $provider->getChartData([], [], ['affiliate', 'partner', 'website']);
        $others = array_pop($data);

        $this->assertEquals(37, $others['value']);
        static::assertStringContainsString('others', $others['source']);
    }

    /**
     * @dataProvider opportunitiesBySourceDataProvider
     */
    public function testAddSmallSourceValuesOverLimitToOthersCategory(array $inputData)
    {
        $data = array_merge(
            [
                // fill up to hardcoded limit of 10
                ['source' => 'source6', 'value' => 10],
                ['source' => 'source7', 'value' => 10],
                ['source' => 'source8', 'value' => 10],
                ['source' => 'source9', 'value' => 10],
                // below sources should be summarized into others category
                ['source' => 'source10', 'value' => 1],
                ['source' => 'source11', 'value' => 2],
                ['source' => 'source12', 'value' => 3],
                ['source' => 'source13', 'value' => 5],
            ],
            $inputData
        );
        $provider = $this->getProvider($data);

        $data = $provider->getChartData([], []);
        $others = array_pop($data);

        $this->assertEquals(11, $others['value']);
        static::assertStringContainsString('others', $others['source']);
    }

    /**
     * @dataProvider opportunitiesBySourceDataProvider
     */
    public function testCreateUnclassifiedCategoryWithEmptySources(array $inputData)
    {
        $provider = $this->getProvider($inputData);
        $data = $provider->getChartData([], []);
        $unclassified = array_shift($data);

        $this->assertEquals(27, $unclassified['value']);
        static::assertStringContainsString('unclassified', $unclassified['source']);
    }

    /**
     * @dataProvider opportunitiesBySourceDataProvider
     */
    public function testFilterOutZeroSources(array $inputData)
    {
        $provider = $this->getProvider($inputData);
        $data = $provider->getChartData([], []);

        $this->assertCount(6, $data);
    }

    public function opportunitiesBySourceDataProvider()
    {
        return [
            ['data' => [
                ['source' => 'direct_mail', 'value' => 15],
                ['source' => 'affiliate', 'value' => 19],
                ['source' => null, 'value' => 27],
                ['source' => 'partner', 'value' => 6],
                ['source' => 'calls', 'value' => 0],
                ['source' => 'website', 'value' => 12],
                ['source' => 'email_marketing', 'value' => 10],
            ]]
        ];
    }

    /**
     * @param array $data
     * @return WidgetOpportunityByLeadSourceProvider
     */
    private function getProvider(array $data)
    {
        $doctrine = $this->getDoctrineMock($data);

        /** @var AclHelper|\PHPUnit\Framework\MockObject\MockObject $aclHelper */
        $aclHelper = $this->getMockBuilder(AclHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var DateFilterProcessor|\PHPUnit\Framework\MockObject\MockObject $processor */
        $processor = $this->getMockBuilder(DateFilterProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var TranslatorInterface|\PHPUnit\Framework\MockObject\MockObject $translator */
        $translator = $this->getMockBuilder(TranslatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $translator->expects($this->any())
            ->method('trans')
            ->will($this->returnArgument(0));

        /** @var EnumExtension|\PHPUnit\Framework\MockObject\MockObject $enumTranslator */
        $enumTranslator = $this->getMockBuilder(EnumExtension::class)
            ->disableOriginalConstructor()
            ->getMock();

        $enumTranslator->expects($this->any())
            ->method('transEnum')
            ->will($this->returnArgument(0));

        /** @var CurrencyQueryBuilderTransformerInterface|\PHPUnit\Framework\MockObject\MockObject $qbTransformer */
        $qbTransformer = $this->getMockBuilder(CurrencyQueryBuilderTransformerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        return new WidgetOpportunityByLeadSourceProvider(
            $doctrine,
            $aclHelper,
            $processor,
            $translator,
            $enumTranslator,
            $qbTransformer
        );
    }

    /**
     * @param array $data
     * @return Registry|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getDoctrineMock(array $data)
    {
        $repo = $this->getMockBuilder(OpportunityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $repo->expects($this->any())
            ->method('getOpportunitiesCountGroupByLeadSource')
            ->will($this->returnValue($data));

        $doctrine = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $doctrine->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($repo));

        return $doctrine;
    }
}
