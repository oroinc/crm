<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Oro\Bundle\AddressBundle\Entity\Region as BAPRegion;
use Oro\Bundle\MagentoBundle\Entity\Region;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadRegions extends AbstractFixture implements ContainerAwareInterface
{
    /** @var ObjectRepository */
    protected $regionRepository;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->regionRepository = $manager->getRepository('OroMagentoBundle:Region');

        $dir = $this->container
            ->get('kernel')
            ->locateResource('@OroMagentoBundle/Migrations/Data/ORM');

        if (($handler = fopen($dir . '/data/regions.csv', 'r')) !== false) {
            $header = fgetcsv($handler, 0, ",");

            while (($data = fgetcsv($handler, 0, ',')) !== false) {
                $manager->persist($this->getRegion(array_combine(array_values($header), array_values($data))));
            }
            # Close the File.
            fclose($handler);
        }

        $manager->flush();
        $manager->clear();
    }

    /**
     * @param array $regionData
     *
     * @return null|Region
     */
    protected function getRegion(array $regionData)
    {
        if (strpos($regionData['code'], $regionData['country_id'] . BAPRegion::SEPARATOR) === 0) {
            $combinedCode = $regionData['code'];
        } else {
            $combinedCode = BAPRegion::getRegionCombinedCode($regionData['country_id'], $regionData['code']);
        }

        /** @var $region Region */
        $region = $this->regionRepository->findOneBy(array('combinedCode' => $combinedCode));
        if (!$region) {
            $region = new Region($combinedCode);
            $region->setCode($regionData['code'])
                ->setRegionId($regionData['region_id'])
                ->setCombinedCode($combinedCode)
                ->setCountryCode($regionData['country_id']);
        }

        $region->setName($regionData['default_name']);

        return $region;
    }
}
