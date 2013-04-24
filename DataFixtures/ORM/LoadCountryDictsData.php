<?php

namespace Oro\Bundle\AddressBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\AddressBundle\Entity\Country;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\AddressBundle\Provider\ImportExport\Manager;
use Oro\Bundle\AddressBundle\Entity;


class LoadCountryDictsData extends AbstractFixture implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Load address types
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        /**
         * @var $importManager Manager
         */
        $importManager = $this->container->get('oro_address.dict.import.manager');
        $importManager->sync(
            array(
                new Country('Ukraine', 'UA', 'UKR'),
                new Country('United States of America', 'US', 'USA'),
                new Country('Russian Federation', 'RU', 'RUS'),
            )
        );

    }
}
