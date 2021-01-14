<?php
namespace Oro\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\ContactBundle\Entity\Group;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadContactGroupData extends AbstractFixture implements ContainerAwareInterface, DependentFixtureInterface
{
    /** @var ContainerInterface */
    private $container;

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return ['Oro\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadUserData'];
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        $entityManager = $this->container->get('doctrine.orm.entity_manager');

        $saleUser = $this->getUser($manager, 'sale');

        $groups = array(
            'Behavioural Segmentation' =>  $saleUser,
            'Demographic Segmentation' =>  $this->getUser($manager, 'marketing'),
            'Geographic Segmentation' => $saleUser,
            'Segmentation by occasions' => $saleUser,
            'Segmentation by benefits'  => $saleUser,
        );

        foreach ($groups as $group => $user) {
            $contactGroup = new Group($group);
            $contactGroup->setOwner($user);
            $contactGroup->setOrganization($this->getReference('default_organization'));
            $entityManager->persist($contactGroup);
        }
        $entityManager->flush();
    }

    /**
     * @param ObjectManager $manager
     * @param $userName
     * @return User
     */
    protected function getUser(ObjectManager $manager, $userName)
    {
        return $manager->getRepository('OroUserBundle:User')->findOneBy(['username' => $userName]);
    }
}
