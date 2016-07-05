<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Unit\Provider;

use Doctrine\Bundle\DoctrineBundle\Registry;

use Symfony\Component\Translation\TranslatorInterface;

use Oro\Bundle\DashboardBundle\Filter\DateFilterProcessor;
use Oro\Bundle\EntityExtendBundle\Twig\EnumExtension;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

use OroCRM\Bundle\SalesBundle\Dashboard\Provider\WidgetOpportunityByLeadSourceProvider;
use OroCRM\Bundle\SalesBundle\Entity\Repository\OpportunityRepository;

class WidgetOpportunityByLeadSourceProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider opportunitiesBySourceDataProvider
     *
     * @param array $inputData
     */
    public function testSortByValue(array $inputData)
    {
        $provider = $this->getProvider($inputData);
        $data = $provider->getChartData([], []);

        $this->assertArraySubset(
            [
                1 => ['source' => 'affiliate', 'value' => 19],
                2 => ['source' => 'direct_mail', 'value' => 15],
                3 => ['source' => 'website', 'value' => 12],
            ],
            $data
        );
    }

    /**
     * @dataProvider opportunitiesBySourceDataProvider
     *
     * @param array $inputData
     */
    public function testCreateOthersCategoryWithExcludedSources(array $inputData)
    {
        $provider = $this->getProvider($inputData);
        $data = $provider->getChartData([], [], ['affiliate', 'partner', 'website']);
        $others = array_pop($data);

        $this->assertEquals(37, $others['value']);
        $this->assertContains('others', $others['source']);
    }

    /**
     * @dataProvider opportunitiesBySourceDataProvider
     *
     * @param array $inputData
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
        $this->assertContains('others', $others['source']);
    }

    /**
     * @dataProvider opportunitiesBySourceDataProvider
     *
     * @param array $inputData
     */
    public function testCreateUnclassifiedCategoryWithEmptySources(array $inputData)
    {
        $provider = $this->getProvider($inputData);
        $data = $provider->getChartData([], []);
        $unclassified = array_shift($data);

        $this->assertEquals(27, $unclassified['value']);
        $this->assertContains('unclassified', $unclassified['source']);
    }

    /**
     * @dataProvider opportunitiesBySourceDataProvider
     *
     * @param array $inputData
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

        /** @var AclHelper|\PHPUnit_Framework_MockObject_MockObject $aclHelper */
        $aclHelper = $this->getMockBuilder(AclHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var DateFilterProcessor|\PHPUnit_Framework_MockObject_MockObject $processor */
        $processor = $this->getMockBuilder(DateFilterProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var TranslatorInterface|\PHPUnit_Framework_MockObject_MockObject $translator */
        $translator = $this->getMockBuilder(TranslatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $translator->expects($this->any())
            ->method('trans')
            ->will($this->returnArgument(0));

        /** @var EnumExtension|\PHPUnit_Framework_MockObject_MockObject $enumTranslator */
        $enumTranslator = $this->getMockBuilder(EnumExtension::class)
            ->disableOriginalConstructor()
            ->getMock();

        $enumTranslator->expects($this->any())
            ->method('transEnum')
            ->will($this->returnArgument(0));

        return new WidgetOpportunityByLeadSourceProvider(
            $doctrine,
            $aclHelper,
            $processor,
            $translator,
            $enumTranslator
        );
    }

    /**
     * @param array $data
     * @return Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getDoctrineMock(array $data)
    {
        $repo = $this->getMockBuilder(OpportunityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $repo->expects($this->any())
            ->method('getOpportunitiesAmountGroupByLeadSource')
            ->will($this->returnValue($data));

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
