<?php
namespace OroCRM\Bundle\DemoDataBundle\DataFixtures;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use OroCRM\Bundle\ContactBundle\Entity\Group;

class LoadContactGroupData extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
{
    /** @var ContainerInterface */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        $entityManager = $this->container->get('doctrine.orm.entity_manager');

        $groups = array(
            'Behavioural Segmentation' =>  $this->getReference('default_sale'),
            'Demographic Segmentation' =>  $this->getReference('default_marketing'),
            'Geographic Segmentation' => $this->getReference('default_sale'),
            'Segmentation by occasions' => $this->getReference('default_sale'),
            'Segmentation by benefits'  => $this->getReference('default_sale'),
        );

        foreach ($groups as $group => $user) {
            $contactGroup = new Group($group);
            $contactGroup->setOwner($user);
            $entityManager->persist($contactGroup);
        }
        $entityManager->flush();
    }

    public function getOrder()
    {
        return 120;
    }
}
