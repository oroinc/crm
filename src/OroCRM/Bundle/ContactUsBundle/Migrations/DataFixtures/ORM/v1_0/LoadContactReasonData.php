<?php

namespace OroCRM\Bundle\ContactUsBundle\Migrations\DataFixtures\ORM\v1_0;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;

use OroCRM\Bundle\ContactUsBundle\Entity\ContactReason;

class LoadContactReasonData extends AbstractFixture
{
    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $data = [
            'Want to know more about the product',
            'Interested in partnership',
            'Need help or assistance',
            'Have a complaint',
            'Other'
        ];

        foreach ($data as $methodLabel) {
            $method = new ContactReason($methodLabel);
            $manager->persist($method);
        }

        $manager->flush();
    }
}
