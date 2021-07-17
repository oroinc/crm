<?php

namespace Oro\Bundle\ContactUsBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\ContactUsBundle\Entity\ContactReason;

class LoadContactReasonData extends AbstractFixture
{
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
