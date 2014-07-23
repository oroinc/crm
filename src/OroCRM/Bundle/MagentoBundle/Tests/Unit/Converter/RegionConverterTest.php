<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Converter;

use Doctrine\Common\Persistence\ObjectRepository;

use Oro\Bundle\AddressBundle\Entity\Region as BAPRegion;

use OroCRM\Bundle\MagentoBundle\Entity\Region;
use OroCRM\Bundle\MagentoBundle\Entity\Address;
use OroCRM\Bundle\MagentoBundle\Converter\RegionConverter;

class RegionConverterTest extends \PHPUnit_Framework_TestCase
{
    const TEST_MAGENTO_REGION_ID    = 123;
    const TEST_REGION_COMBINED_CODE = 'UA-61';
    const TEST_REGION_NAME          = 'Kharkivs\'ka oblast\'';

    /** @var ObjectRepository|\PHPUnit_Framework_MockObject_MockObject */
    protected $repository;

    /** @var RegionConverter */
    protected $converter;

    public function setUp()
    {
        $this->repository = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectRepository')
            ->disableOriginalConstructor()->getMock();
        $this->converter  = new RegionConverter($this->repository);
    }

    public function tearDown()
    {
        unset($this->repository, $this->converter);
    }

    /**
     * @dataProvider sourceDataProvider
     *
     * @param mixed  $source
     * @param bool   $foundInDatabase
     * @param array  $expectedResult
     * @param string $exception
     */
    public function testToMagentoData($source, $foundInDatabase, $expectedResult, $exception = null)
    {
        if ($exception) {
            $this->setExpectedException($exception);
        }

        if ($foundInDatabase === true) {
            $region = new Region();
            $region->setRegionId(self::TEST_MAGENTO_REGION_ID);

            $this->repository->expects($this->once())->method('findOneBy')
                ->will($this->returnValue($region));
        } elseif ($foundInDatabase === false) {
            $this->repository->expects($this->once())->method('findOneBy')
                ->will($this->returnValue(null));
        }

        // more than one call should not provoke expectation errors
        $this->assertSame($expectedResult, $this->converter->toMagentoData($source));
        $this->assertSame($expectedResult, $this->converter->toMagentoData($source));
    }

    /**
     * @return array
     */
    public function sourceDataProvider()
    {
        $address1  = new Address();
        $address2  = new Address();
        $badSource = null;

        $region1 = new BAPRegion(self::TEST_REGION_COMBINED_CODE);
        $region1->setName(self::TEST_REGION_NAME);
        $address1->setRegion($region1);

        $address2->setRegionText(self::TEST_REGION_NAME);

        return [
            'take region from address, found in magento DB, use region ID'       => [
                $address1,
                true,
                ['region' => null, 'region_id' => self::TEST_MAGENTO_REGION_ID]
            ],
            'take region from address, not found in magento DB, use region name' => [
                $address1,
                false,
                ['region' => self::TEST_REGION_NAME, 'region_id' => null]
            ],
            'region not set, use region text'                                    => [
                $address2,
                null,
                ['region' => self::TEST_REGION_NAME, 'region_id' => null]
            ],
            'bad type given'                                                     => [
                $badSource,
                null,
                null,
                '\InvalidArgumentException'
            ]
        ];
    }
}
