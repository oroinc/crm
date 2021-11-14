<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Provider;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\EntityExtendBundle\Extend\RelationType;
use Oro\Bundle\SalesBundle\Entity\Customer;
use Oro\Bundle\SalesBundle\Provider\EntityVariablesProvider;

class EntityVariablesProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var EntityVariablesProvider */
    private $variableProvider;

    protected function setUp(): void
    {
        $this->variableProvider = new EntityVariablesProvider();
    }

    public function testGetVariableGetters(): void
    {
        $this->assertEquals(
            [Customer::class => ['account' => 'getAccount']],
            $this->variableProvider->getVariableGetters()
        );
    }

    public function testGetVariableDefinitions(): void
    {
        $expected = [
            Customer::class => [
                'account' => [
                    'type' => RelationType::TO_ONE,
                    'label' => 'Account',
                    'related_entity_name' => Account::class
                ]
            ]
        ];

        $this->assertEquals($expected, $this->variableProvider->getVariableDefinitions());
    }

    public function testGetVariableProcessors(): void
    {
        $this->assertEmpty($this->variableProvider->getVariableProcessors('acme'));
    }
}
