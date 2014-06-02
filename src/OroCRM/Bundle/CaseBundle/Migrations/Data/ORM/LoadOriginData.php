<?php

namespace OroCRM\Bundle\CaseBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use OroCRM\Bundle\CaseBundle\Entity\CaseOrigin;

class LoadOriginData extends AbstractFixture
{
    /**
     * Load default origins
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $origins = array(
            CaseOrigin::TYPE_PHONE => 'Phone',
            CaseOrigin::TYPE_EMAIL => 'Email',
            CaseOrigin::TYPE_WEB   => 'Web',
            CaseOrigin::TYPE_OTHER => 'Other'
        );
        foreach ($origins as $type => $label) {
            $origin = new CaseOrigin();
            $origin->setType($type);
            $origin->setLabel($label);
            $manager->persist($origin);
        }
        $manager->flush();
    }
}
