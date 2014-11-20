<?php

namespace OroCRM\Bundle\CallBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\ActivityListBundle\Migrations\Data\ORM\AddActivityListsData;

class AddCallsActivityLists extends AddActivityListsData implements DependentFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return ['OroCRM\Bundle\CallBundle\Migrations\Data\ORM\UpdateCallWithOrganization'];
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->addActivityListsForActivityClass(
            $manager,
            'OroCRMCallBundle:Call',
            'owner',
            'organization'
        );
    }
}
