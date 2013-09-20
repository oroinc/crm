<?php

namespace OroCRM\Bundle\ReportBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\SalesBundle\Entity\Opportunity;
use OroCRM\Bundle\SalesBundle\Entity\OpportunityCloseReason;
use OroCRM\Bundle\SalesBundle\Entity\OpportunityStatus;

class LoadOpportunities implements FixtureInterface
{
    /**
     * Load data fixtures
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $account = new Account();
        $account->setName('acc name');

        $closeReason = new OpportunityCloseReason('close reason '.uniqid());
        $closeReason->setLabel('label '.uniqid());

        $statuses = array('new', 'qualified', 'canceled');
        $status = new OpportunityStatus($statuses[rand(0,2)]);


        $opportunity = new Opportunity();
        $opportunity->setAccount($account)
            ->setCloseReason($closeReason)
            ->setStatus($status)
            ->setName('oppo name '.uniqid())
            ->setCloseRevenue(range(0, 1000))
            ->setBudgetAmount(range(0, 1000));

    }
}
