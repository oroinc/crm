<?php

namespace Oro\Bundle\AddressBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\TextType;

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
        $fm = $this->container->get('oro_address.address.manager');
        $sm = $fm->getStorageManager();

        $attr = $fm
            ->createAttribute(new TextType())
            ->setCode('firstname');

        $sm->persist($attr);

        $attr = $fm
            ->createAttribute(new TextType())
            ->setCode('lastname');

        $sm->persist($attr);

        $attr = $fm
            ->createAttribute(new TextType())
            ->setCode('company');

        $sm->persist($attr);
        $sm->flush();
    }
}
