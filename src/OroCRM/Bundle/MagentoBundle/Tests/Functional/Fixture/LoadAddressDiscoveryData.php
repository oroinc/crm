<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Functional\Fixture;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadAddressDiscoveryData extends AbstractFixture
{
    /**
     * @var array
     */
    protected $data = [];

    /**
     * {@inheritdoc}
     */
    function load(ObjectManager $manager)
    {
    }
}
