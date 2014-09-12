<?php

namespace OroCRM\Bundle\ReportBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\EntityExtendBundle\Entity\Repository\EnumValueRepository;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;

class LoadLeadSourceData extends AbstractFixture implements OrderedFixtureInterface
{
    /** @var array */
    protected $data = [
        'Website'         => false,
        'Direct Mail'     => false,
        'Affiliate'       => false,
        'Email Marketing' => false,
        'Outbound'        => false,
        'Partner'         => false
    ];

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $className = ExtendHelper::buildEnumValueClassName('lead_source');

        /** @var EnumValueRepository $enumRepo */
        $enumRepo = $manager->getRepository($className);

        $priority = 1;
        foreach ($this->data as $name => $isDefault) {
            $enumOption = $enumRepo->createEnumValue($name, $priority++, $isDefault);
            $manager->persist($enumOption);
        }

        $manager->flush();
    }

    public function getOrder()
    {
        return 290;
    }
}
