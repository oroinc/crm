<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Handler;

use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\SalesBundle\Handler\OpportunityApiHandler;

class OpportunityApiHandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider probabilityDataProvider
     */
    public function testShouldAppendProbabilityToChangeSet(float $probability)
    {
        $changeSet = [
            'fields' => [
                'probability' => $probability,
            ],
        ];

        $opportunity = new Opportunity();
        $opportunity->setProbability($probability);

        $handler = new OpportunityApiHandler();

        $this->assertEquals($changeSet, $handler->afterProcess($opportunity));
    }

    public function probabilityDataProvider(): array
    {
        return [
            [0.0], [0.1], [0.5], [1.0]
        ];
    }
}
