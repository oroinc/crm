<?php

namespace Oro\Bundle\AddressBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadAddressAttributesData extends AbstractFixture implements ContainerAwareInterface
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
         * @var $fm \Oro\Bundle\AddressBundle\Entity\Manager\AddressManager
         */
        $fm = $this->container->get('oro_address.address.provider')->getStorage();
        /** @var \Doctrine\Common\Persistence\ObjectManager $sm */
        $sm = $fm->getStorageManager();

        $attr = $fm
            ->createAttribute('oro_flexibleentity_text')
            ->setCode('firstname')
            ->setLabel('First name');

        $sm->persist($attr);

        $attr = $fm
            ->createAttribute('oro_flexibleentity_text')
            ->setCode('lastname')
            ->setLabel('Last name');

        $sm->persist($attr);

        $attr = $fm
            ->createAttribute('oro_flexibleentity_text')
            ->setCode('company')
            ->setLabel('Company');

        $sm->persist($attr);
        $sm->flush();
    }
}
