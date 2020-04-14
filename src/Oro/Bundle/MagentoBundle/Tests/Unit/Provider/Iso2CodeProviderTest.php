<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Provider;

use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Repository\CountryRepository;
use Oro\Bundle\MagentoBundle\Provider\Iso2CodeProvider;

class Iso2CodeProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject
     */
    private $registry;

    /**
     * @var Iso2CodeProvider
     */
    private $provider;

    protected function setUp(): void
    {
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->provider = new Iso2CodeProvider($this->registry);
    }

    /**
     * @dataProvider getIso2CodeByCountryIdProvider
     * @param string $countryId
     * @param string $expectedIso2Code
     */
    public function testGetIso2CodeByCountryId($countryId, $expectedIso2Code)
    {
        $countriesArray = [
            [
                'iso2Code' => 'US',
                'iso3Code' => 'USA',
                'name' => 'United States',
            ],
            [
                'iso2Code' => 'GB',
                'iso3Code' => 'GBR',
                'name' => 'United Kingdom',
            ],
        ];
        $countryRepository = $this->getMockBuilder(CountryRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $countryRepository->expects($this->once())
            ->method('getAllCountryNamesArray')
            ->willReturn($countriesArray);
        $this->registry->expects($this->once())
            ->method('getRepository')
            ->with(Country::class)
            ->willReturn($countryRepository);

        $iso2Code = $this->provider->getIso2CodeByCountryId($countryId);
        $this->assertEquals($expectedIso2Code, $iso2Code);
    }

    /**
     * @return array
     */
    public function getIso2CodeByCountryIdProvider()
    {
        return [
            'find by iso2' => [
                'countryId' => 'US',
                'expectedIso2Code' => 'US',
            ],
            'find by iso3' => [
                'countryId' => 'GBR',
                'expectedIso2Code' => 'GB',
            ],
            'find by name' => [
                'countryId' => 'United Kingdom',
                'expectedIso2Code' => 'GB'
            ],
            'cannot find' => [
                'countryId' => 'SomeCountryId',
                'expectedIso2Code' => null,
            ],
        ];
    }
}
