<?php

namespace OroCRM\Bundle\ContactBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use OroCRM\Bundle\ContactBundle\Entity\Source;

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

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
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
