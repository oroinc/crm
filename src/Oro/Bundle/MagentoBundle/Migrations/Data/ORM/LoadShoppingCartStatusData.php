<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\MagentoBundle\Entity\CartStatus;
use Oro\Bundle\MigrationBundle\Fixture\LoadedFixtureVersionAwareInterface;
use Oro\Bundle\MigrationBundle\Fixture\VersionedFixtureInterface;

class LoadShoppingCartStatusData extends AbstractFixture implements
    VersionedFixtureInterface,
    LoadedFixtureVersionAwareInterface
{
    /** @var string */
    private $version;

    /** @var array */
    protected $dataV0 = array(
        'open'                     => 'Open',
        'lost'                     => 'Lost',
        'converted_to_opportunity' => 'Converted to Opportunity',
    );

    /** @var array */
    protected $dataV1 = array(
        CartStatus::STATUS_EXPIRED => 'Expired',
        CartStatus::STATUS_PURCHASED => 'Purchased',
    );

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $data = [];

        // fixture was not versioned before
        // logic is following:
        // if version is null then it's first installation - load all statuses
        // if version is 0.0 then it's non-version fixture installed, load only new statuses
        if (!$this->version) {
            $data = array_merge($this->dataV0, $this->dataV1);
        } elseif ($this->version === '0.0') {
            $data = $this->dataV1;
        } elseif ($this->version === '1.0') {
            // remove not needed status from version 1.0
            $converted = $manager->find('Oro\Bundle\MagentoBundle\Entity\CartStatus', 'converted');
            if ($converted) {
                $manager->remove($converted);
            }
        }

        foreach ($data as $name => $label) {
            $method = new CartStatus($name);
            $method->setLabel($label);
            $manager->persist($method);
        }

        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function setLoadedVersion($version = null)
    {
        $this->version = $version;
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion()
    {
        return '1.1';
    }
}
