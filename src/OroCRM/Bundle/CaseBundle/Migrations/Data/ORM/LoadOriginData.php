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
            CaseOrigin::CODE_PHONE => 'Phone',
            CaseOrigin::CODE_EMAIL => 'Email',
            CaseOrigin::CODE_WEB   => 'Web',
            CaseOrigin::CODE_OTHER => 'Other'
        );
        foreach ($origins as $code => $label) {
            $origin = new CaseOrigin();
            $origin->setCode($code);
            $origin->setLabel($label);
            $manager->persist($origin);
        }
        $manager->flush();
    }
}
