<?php

namespace Oro\Bundle\ContactBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\ContactBundle\Entity\Source;

class LoadSourceData extends AbstractFixture
{
    /**
     * @var array
     */
    protected $data = array(
        'call'    => 'Phone Call',
        'tv'      => 'TV',
        'website' => 'Website',
        'other'   => 'Other Source',
    );

    public function load(ObjectManager $manager)
    {
        foreach ($this->data as $sourceName => $sourceLabel) {
            $source = new Source($sourceName);
            $source->setLabel($sourceLabel);
            $manager->persist($source);
        }

        $manager->flush();
    }
}
