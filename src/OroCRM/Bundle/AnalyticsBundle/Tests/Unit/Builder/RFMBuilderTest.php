<?php

namespace OroCRM\Bundle\AnalyticsBundle\Tests\Unit\Builder;

use Doctrine\ORM\EntityRepository;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use OroCRM\Bundle\AnalyticsBundle\Builder\RFMBuilder;
use OroCRM\Bundle\AnalyticsBundle\Builder\RFMProviderInterface;
use OroCRM\Bundle\AnalyticsBundle\Entity\RFMMetricCategory;
use OroCRM\Bundle\AnalyticsBundle\Model\RFMAwareInterface;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;

class RFMBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RFMBuilder
     */
    protected $builder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|DoctrineHelper
     */
    protected $doctrineHelper;

    protected function setUp()
    {
        $this->doctrineHelper = $this->getMockBuilder('Oro\Bundle\EntityBundle\ORM\DoctrineHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->builder = new RFMBuilder($this->doctrineHelper);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Expected one of "recency,frequency,monetary" type, "wrong_type" given
     */
    public function testAddProviderFailed()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|RFMProviderInterface $provider */
        $provider = $this->getMock('OroCRM\Bundle\AnalyticsBundle\Builder\RFMProviderInterface');
        $provider->expects($this->once())
            ->method('getType')
            ->will($this->returnValue('wrong_type'));

        $this->builder->addProvider($provider);
    }

    public function testAddProviderSuccess()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|RFMProviderInterface $provider */
        $provider = $this->getMock('OroCRM\Bundle\AnalyticsBundle\Builder\RFMProviderInterface');
        $provider->expects($this->once())
            ->method('getType')
            ->will($this->returnValue(RFMMetricCategory::TYPE_FREQUENCY));

        $this->builder->addProvider($provider);

        $this->assertEquals(
            [$provider],
            $this->builder->getProviders()
        );
    }

    /**
     * @param mixed $entity
     * @param bool $expected
     *
     * @dataProvider supportsDataProvider
     */
    public function testSupports($entity, $expected)
    {
        $this->assertEquals(
            $expected,
            $this->builder->supports($entity)
        );
    }

    /**
     * @return array
     */
    public function supportsDataProvider()
    {
        return [
            [null, false],
            [new \stdClass(), false],
            [new Customer(), true],
            [$this->getMock('OroCRM\Bundle\AnalyticsBundle\Model\RFMAwareInterface'), true],
        ];
    }

    /**
     * @param bool $supports
     * @param bool $expected
     * @param mixed $providerValue
     * @param mixed $expectedValue
     * @param \PHPUnit_Framework_MockObject_MockObject|Channel $channel
     * @param int $channelId
     * @param array $categories
     *
     * @dataProvider buildDataProvider
     */
    public function testBuild(
        $supports,
        $expected,
        $providerValue = null,
        $expectedValue = null,
        $channel = null,
        $channelId = null,
        array $categories = null
    ) {
        /** @var \PHPUnit_Framework_MockObject_MockObject|RFMProviderInterface $provider */
        $provider = $this->getMock('OroCRM\Bundle\AnalyticsBundle\Builder\RFMProviderInterface');
        $provider->expects($this->any())
            ->method('getType')
            ->will($this->returnValue(RFMMetricCategory::TYPE_FREQUENCY));
        $provider->expects($this->any())
            ->method('getValue')
            ->will($this->returnValue($providerValue));

        $provider->expects($this->any())
            ->method('supports')
            ->will($this->returnValue($supports));

        $this->builder->addProvider($provider);

        /** @var \PHPUnit_Framework_MockObject_MockObject|RFMAwareInterface $entity */
        $entity = $this->getMock('OroCRM\Bundle\AnalyticsBundle\Model\RFMAwareInterface');
        $entity->expects($this->any())
            ->method('getDataChannel')
            ->will($this->returnValue($channel));

        if ($expected) {
            $entity->expects($this->exactly(2))
                ->method('setFrequency')
                ->with($this->equalTo($expectedValue));
        }

        if ($channel) {
            $this->doctrineHelper->expects($this->exactly(2))
                ->method('getSingleEntityIdentifier')
                ->with($this->equalTo($channel))
                ->will($this->returnValue($channelId));
        }

        if ($channel && is_array($categories)) {
            /** @var \PHPUnit_Framework_MockObject_MockObject|EntityRepository $repo */
            $repo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
                ->disableOriginalConstructor()
                ->getMock();

            $this->doctrineHelper->expects($this->once())
                ->method('getEntityRepository')
                ->will($this->returnValue($repo));

            $repo->expects($this->once())
                ->method('findBy')
                ->will($this->returnValue($categories));
        }

        $this->assertEquals(
            $expected,
            $this->builder->build($entity)
        );

        /** check cache, getEntityRepository should not be called anymore */
        $this->assertEquals(
            $expected,
            $this->builder->build($entity)
        );
    }

    /**
     * @return array
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function buildDataProvider()
    {
        $channel = $this->getMock('OroCRM\Bundle\ChannelBundle\Entity\Channel');

        return [
            'no support' => [false, false],
            'empty value' => [true, true],
            'channel without id' => [true, true, null, null, $channel],
            'empty categories' => [true, true, 10, null, $channel, 2, []],
            'value not matched' => [true, true, 20, null, $channel, 2, [$this->getCategoryMock(1, 20)]],
            'value matched' => [true, true, 15, 2, $channel, 2, [$this->getCategoryMock(2, 10)]],
            'first max match' => [
                true,
                true,
                10,
                null,
                $channel,
                2,
                [$this->getCategoryMock(0, 0, 10), $this->getCategoryMock(2, 10)]
            ],
            'first min match' => [
                true,
                true,
                10,
                null,
                $channel,
                2,
                [$this->getCategoryMock(1, 10, 20), $this->getCategoryMock(2, 20)]
            ],
            'first match' => [
                true,
                true,
                11,
                1,
                $channel,
                2,
                [$this->getCategoryMock(1, 10, 20), $this->getCategoryMock(2, 20)]
            ],
            'last max match' => [
                true,
                true,
                30,
                2,
                $channel,
                2,
                [$this->getCategoryMock(1, 10, 20), $this->getCategoryMock(2, 20, 30)]
            ],
            'more than last min match' => [
                true,
                true,
                500,
                2,
                $channel,
                2,
                [$this->getCategoryMock(1, 10, 20), $this->getCategoryMock(2, 20)]
            ],
        ];
    }

    /**
     * @param int $index
     * @param int $minValue
     * @param int $maxValue
     * @return \PHPUnit_Framework_MockObject_MockObject|RFMMetricCategory
     */
    protected function getCategoryMock($index, $minValue, $maxValue = null)
    {
        $category = $this->getMock('OroCRM\Bundle\AnalyticsBundle\Entity\RFMMetricCategory');

        $category->expects($this->any())
            ->method('getMinValue')
            ->will($this->returnValue($minValue));

        $category->expects($this->any())
            ->method('getMaxValue')
            ->will($this->returnValue($maxValue));

        $category->expects($this->any())
            ->method('getIndex')
            ->will($this->returnValue($index));

        return $category;
    }
}
