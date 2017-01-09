<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\ImportExport\EventListener;

use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\SalesBundle\ImportExport\EventListener\OpportunityProbabilitySubscriber;
use Oro\Bundle\ImportExportBundle\Event\DenormalizeEntityEvent;
use Oro\Bundle\ImportExportBundle\Event\NormalizeEntityEvent;

class OpportunityProbabilitySubscriberTest extends \PHPUnit_Framework_TestCase
{
    /** @var OpportunityProbabilitySubscriber */
    protected $subscriber;
    /** @var Opportunity */
    protected $opportunity;

    protected function setUp()
    {
        $this->subscriber  = new OpportunityProbabilitySubscriber();
        $this->opportunity = new Opportunity();
    }

    public function testBeforeNormalize()
    {
        $this->opportunity->setProbability(0.1);
        $event = new NormalizeEntityEvent($this->opportunity, [], false);
        $this->subscriber->beforeNormalize($event);
        $this->assertEquals('10', $this->opportunity->getProbability());
    }

    public function testafterDenormalize()
    {
        $this->opportunity->setProbability('10');
        $event = new DenormalizeEntityEvent($this->opportunity, []);
        $this->subscriber->afterDenormalize($event);
        $this->assertEquals(0.1, $this->opportunity->getProbability());
    }
}
