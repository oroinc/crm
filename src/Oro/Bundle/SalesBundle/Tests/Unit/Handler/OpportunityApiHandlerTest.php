<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Handler;

use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\SalesBundle\Handler\OpportunityApiHandler;

class OpportunityApiHandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider probabilityDataProvider
     *
     * @param float $probability
     */
    public function testShouldAppendProbabilityToChangeset($probability)
    {
        $changeset = [
            'fields' => [
                'probability' => $probability,
            ],
        ];

        $handler = $this->getOpportunityApiHndler([]);
        $opportunity = $this->getOpportunity($probability);

        $this->assertEquals($changeset, $handler->afterProcess($opportunity));
    }

    /**
     * @return array
     */
    public function probabilityDataProvider()
    {
        return [
            [0.0], [0.1], [0.5], [1.0]
        ];
    }

    /**
     * @return array
     */
    private function getDefaultProbilities()
    {
        return [
            'identification_alignment' => 0.3,
            'needs_analysis' => 0.2,
            'solution_development' => 0.5,
            'negotiation' => 0.8,
            'in_progress' => 0.1,
            'won' => 1.0,
            'lost' => 0.0,
        ];
    }

    /**
     * @return OpportunityApiHandler
     */
    private function getOpportunityApiHndler()
    {
        return new OpportunityApiHandler();
    }

    /**
     * @param float $probability
     * @return Opportunity
     */
    private function getOpportunity($probability)
    {
        $opportunity = new Opportunity();
        $opportunity->setProbability($probability);

        return $opportunity;
    }
}
