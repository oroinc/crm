<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Provider;

use Oro\Bundle\EntityBundle\Provider\EntityNameProviderInterface;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Entity\SalesFunnel;
use Oro\Bundle\SalesBundle\Provider\SalesFunnelEntityNameProvider;

class SalesFunnelEntityNameProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var SalesFunnelEntityNameProvider */
    private $provider;

    protected function setUp(): void
    {
        $this->provider = new SalesFunnelEntityNameProvider();
    }

    /**
     * @dataProvider getNameDataProvider
     */
    public function testGetName(string $format, ?string $locale, object $entity, string|false $expected)
    {
        $result = $this->provider->getName($format, $locale, $entity);
        $this->assertSame($expected, $result);
    }

    /**
     * @dataProvider getNameDQLDataProvider
     */
    public function testGetNameDQL(
        string $format,
        ?string $locale,
        string $className,
        string $alias,
        string|false $expected
    ) {
        $result = $this->provider->getNameDQL($format, $locale, $className, $alias);
        $this->assertSame($expected, $result);
    }

    public function getNameDataProvider(): array
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

    public function getNameDQLDataProvider(): array
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

    private function getEntity(): SalesFunnel
    {
        $lead = new Lead();
        $lead->setName('Contact');

        $salesFunnel = new SalesFunnel();
        $salesFunnel->setLead($lead);

        return $salesFunnel;
    }
}
