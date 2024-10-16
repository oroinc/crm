<?php

namespace Oro\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\ContactBundle\Entity\Group;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Loads contact group demo data
 */
class LoadContactGroupData extends AbstractFixture implements ContainerAwareInterface, DependentFixtureInterface
{
    use ContainerAwareTrait;

    #[\Override]
    public function getDependencies(): array
    {
        return [LoadUserData::class];
    }

    #[\Override]
    public function load(ObjectManager $manager): void
    {
        $saleUser = $this->getUser($manager, 'sale');
        $groups = [
            'Behavioural Segmentation' =>  $saleUser,
            'Demographic Segmentation' =>  $this->getUser($manager, 'marketing'),
            'Geographic Segmentation' => $saleUser,
            'Segmentation by occasions' => $saleUser,
            'Segmentation by benefits'  => $saleUser,
        ];
        foreach ($groups as $group => $user) {
            $contactGroup = new Group($group);
            $contactGroup->setOwner($user);
            $contactGroup->setOrganization($this->getReference('default_organization'));
            $manager->persist($contactGroup);
        }
        $manager->flush();
    }

    private function getUser(ObjectManager $manager, string $userName): User
    {
        return $manager->getRepository(User::class)->findOneBy(['username' => $userName]);
    }
}
