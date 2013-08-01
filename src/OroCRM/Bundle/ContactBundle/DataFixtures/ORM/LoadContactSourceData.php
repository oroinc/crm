<?php

namespace OroCRM\Bundle\ContactBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use OroCRM\Bundle\ContactBundle\Entity\ContactSource;

class LoadContactSourceData extends AbstractFixture
{
    /**
     * @var array
     */
    protected $sourceData = array(
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
        foreach ($this->sourceData as $sourceName => $sourceLabel) {
            $source = new ContactSource($sourceName);
            $source->setLabel($sourceLabel);
            $manager->persist($source);
        }

        $manager->flush();
    }
}
