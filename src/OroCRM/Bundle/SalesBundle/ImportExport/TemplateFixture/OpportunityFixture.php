<?php

namespace OroCRM\Bundle\SalesBundle\ImportExport\TemplateFixture;

use Oro\Bundle\ImportExportBundle\TemplateFixture\TemplateFixtureInterface;
use OroCRM\Bundle\SalesBundle\Entity\Lead;
use OroCRM\Bundle\SalesBundle\Entity\Opportunity;
use OroCRM\Bundle\SalesBundle\Entity\OpportunityStatus;

class OpportunityFixture implements TemplateFixtureInterface
{
    /**
     * @var TemplateFixtureInterface
     */
    protected $leadFixture;

    /**
     * @param TemplateFixtureInterface $leadFixture
     */
    public function __construct(TemplateFixtureInterface $leadFixture)
    {
        $this->leadFixture = $leadFixture;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        /** @var Lead $lead */
        $lead = $this->leadFixture->getData()->current();

        $opportunity = new Opportunity();
        $opportunity
            ->setName('Oro Inc. Opportunity Name')
            ->setAccount($lead->getAccount())
            ->setCreatedAt(new \DateTime())
            ->setUpdatedAt(new \DateTime())
            ->setOwner($lead->getOwner())
            ->setBudgetAmount(1000000)
            ->setContact($lead->getContact())
            ->setLead($lead)
            ->setStatus(new OpportunityStatus('In Progress'));

        return new \ArrayIterator(array($opportunity));
    }
}
