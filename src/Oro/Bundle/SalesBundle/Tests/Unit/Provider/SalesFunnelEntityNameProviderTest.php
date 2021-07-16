<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Provider;

use Oro\Bundle\EntityBundle\Provider\EntityNameProviderInterface;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Entity\SalesFunnel;
use Oro\Bundle\SalesBundle\Provider\SalesFunnelEntityNameProvider;
use Oro\Component\DependencyInjection\ServiceLink;

class SalesFunnelEntityNameProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var SalesFunnelEntityNameProvider */
    protected $provider;

    /** @var ServiceLink */
    protected $nameFormatterLink;

    /** @var ServiceLink */
    protected $dqlNameFormatterLink;

    protected function setUp(): void
    {
        $this->nameFormatterLink = $this->createMock(ServiceLink::class);

        $this->dqlNameFormatterLink = $this->createMock(ServiceLink::class);

        $this->provider = new SalesFunnelEntityNameProvider($this->nameFormatterLink, $this->dqlNameFormatterLink);
    }

    /**
     * @dataProvider getNameDataProvider
     */
    public function testGetName($format, $locale, $entity, $expected)
    {
        $result = $this->provider->getName($format, $locale, $entity);
        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider getNameDQLDataProvider
     */
    public function testGetNameDQL($format, $locale, $className, $alias, $expected)
    {
        $result = $this->provider->getNameDQL($format, $locale, $className, $alias);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function getNameDataProvider()
    {
        return [
            'test unsupported class' => [
                'format' => '',
                'locale' => null,
                'entity' => new \stdClass(),
                'expected' => false
            ],
            'test unsupported format' => [
                'format' => '',
                'locale' => null,
                'entity' => $this->getEntity(),
                'expected' => false
            ],
            'correct data' => [
                'format' => SalesFunnelEntityNameProvider::FULL,
                'locale' => '',
                'entity' => $this->getEntity(),
                'expected' => 'Contact'
            ]
        ];
    }

    /**
     * @return array
     */
    public function getNameDQLDataProvider()
    {
        return [
            'test unsupported class Name' => [
                'format' => '',
                'locale' => null,
                'className' => '',
                'alias' => '',
                'expected' => false
            ],
            'test unsupported format' => [
                'format' => '',
                'locale' => null,
                'className' => SalesFunnelEntityNameProvider::CLASS_NAME,
                'alias' => '',
                'expected' => false
            ],
            'correct data' => [
                'format' => EntityNameProviderInterface::FULL,
                'locale' => null,
                'className' => SalesFunnelEntityNameProvider::CLASS_NAME,
                'alias' => '',
                'expected' => 'CONCAT(\'Sales Funnel \', .id)'
            ]
        ];
    }

    /**
     * @return SalesFunnel
     */
    protected function getEntity()
    {
        $lead = new Lead();
        $lead->setName('Contact');

        $salesFunnel = new SalesFunnel();
        $salesFunnel->setLead($lead);

        return $salesFunnel;
    }
}
