<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\EntityExtendBundle\Entity\Repository\EnumValueRepository;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;

class LoadOpportunityStateData extends AbstractFixture
{
    /** @var array */
    protected $data = [
        'Identification & Alignment' => 'identification_alignment',
        'Needs Analysis' => 'needs_analysis',
        'Solution Development' => 'solution_development',
        'Negotiation' => 'negotiation',
        'Closed Won' => 'won',
        'Closed Lost' => 'lost'
    ];

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $className = ExtendHelper::buildEnumValueClassName('opportunity_state');

        /** @var EnumValueRepository $enumRepo */
        $enumRepo = $manager->getRepository($className);

        $priority = 1;
        foreach ($this->data as $name => $id) {
            $enumOption = $enumRepo->createEnumValue($name, $priority++, false, $id);
            $manager->persist($enumOption);
        }

        $manager->flush();
    }
}
