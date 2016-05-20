<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Unit\Provider;

use Oro\Bundle\EntityBundle\Provider\EntityNameProviderInterface;
use Oro\Bundle\EntityConfigBundle\DependencyInjection\Utils\ServiceLink;

use OroCRM\Bundle\SalesBundle\Entity\Lead;
use OroCRM\Bundle\SalesBundle\Entity\SalesFunnel;
use OroCRM\Bundle\SalesBundle\Provider\SalesFunnelEntityNameProvider;

class SalesFunnelEntityNameProviderTest extends \PHPUnit_Framework_TestCase
{
    /** @var SalesFunnelEntityNameProvider */
    protected $provider;

    /** @var ServiceLink */
    protected $nameFormatterLink;

    /** @var ServiceLink */
    protected $dqlNameFormatterLink;

    protected function setUp()
    {
        $this->nameFormatterLink = $this
            ->getMockBuilder('Oro\Bundle\EntityConfigBundle\DependencyInjection\Utils\ServiceLink')
            ->disableOriginalConstructor()->getMock();

        $this->dqlNameFormatterLink = $this
            ->getMockBuilder('Oro\Bundle\EntityConfigBundle\DependencyInjection\Utils\ServiceLink')
            ->disableOriginalConstructor()->getMock();

        $this->provider = new SalesFunnelEntityNameProvider($this->nameFormatterLink, $this->dqlNameFormatterLink);
    }

    /**
     * @dataProvider getNameDataProvider
     *
     * @param $format
     * @param $locale
     * @param $entity
     * @param $expected
     */
    public function testGetName($format, $locale, $entity, $expected)
    {
        $result = $this->provider->getName($format, $locale, $entity);
        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider getNameDQLDataProvider
     *
     * @param $format
     * @param $locale
     * @param $className
     * @param $alias
     * @param $expected
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
