<?php
namespace OroCRM\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManager;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\DataAuditBundle\Tests\Unit\Fixture\Repository\UserRepository;
use Oro\Bundle\TagBundle\Entity\TagManager;
use Oro\Bundle\OrganizationBundle\Entity\BusinessUnit;
use Oro\Bundle\UserBundle\Migrations\Data\ORM\LoadRolesData;

class LoadUserData extends AbstractFixture implements ContainerAwareInterface, DependentFixtureInterface
{
    /** @var ContainerInterface */
    private $container;

    /** @var  EntityRepository */
    protected $roles;

    /** @var  EntityRepository */
    protected $group;

    /** @var UserRepository */
    protected $user;

    /** @var  TagManager */
    protected $tagManager;

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            'OroCRM\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadGroupData',
            'OroCRM\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadBusinessUnitData'
        ];
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        /** @var  EntityManager $entityManager */
        $entityManager = $container->get('doctrine.orm.entity_manager');
        $this->roles = $entityManager->getRepository('OroUserBundle:Role');
        $this->group = $entityManager->getRepository('OroUserBundle:Group');
        $this->user = $entityManager->getRepository('OroUserBundle:User');
        $this->tagManager = $container->get('oro_tag.tag.manager');

    }

    public function load(ObjectManager $manager)
    {
        $organization = $this->getReference('default_organization');

        /** @var \Oro\Bundle\UserBundle\Entity\Role $marketingRole */
        $marketingRole = $this->roles->findOneBy(array('role' => 'ROLE_MARKETING_MANAGER'));
        /** @var \Oro\Bundle\UserBundle\Entity\Role $saleRole */
        $saleRole = $this->roles->findOneBy(array('role' => LoadRolesData::ROLE_MANAGER));
        /** @var \Oro\Bundle\UserBundle\Entity\Group $salesGroup */
        $salesGroup = $this->group->findOneBy(array('name' => 'Executive Sales'));
        /** @var \Oro\Bundle\UserBundle\Entity\Group $marketingGroup */
        $marketingGroup = $this->group->findOneBy(array('name' => 'Executive Marketing'));

        /** @var \Oro\Bundle\UserBundle\Entity\UserManager $userManager */
        $userManager = $this->container->get('oro_user.manager');

        $sale = $userManager->createUser();

        $sale
            ->setUsername('sale')
            ->setPlainPassword('sale')
            ->setFirstName('Ellen')
            ->setLastName('Rowell')
            ->addRole($saleRole)
            ->addGroup($salesGroup)
            ->setEmail('sale@example.com')
            ->setOrganization($organization)
            ->addOrganization($organization)
            ->setBusinessUnits(
                new ArrayCollection(
                    array(
                        $this->getBusinessUnit($manager, 'Acme, General'),
                        $this->getBusinessUnit($manager, 'Acme, East'),
                        $this->getBusinessUnit($manager, 'Acme, West')
                    )
                )
            );

        if ($this->hasReference('default_main_business')) {
            $sale->setOwner($this->getBusinessUnit($manager, 'Acme, General'));
        }
        $this->addReference('default_sale', $sale);
        $userManager->updateUser($sale);

        /** @var \Oro\Bundle\UserBundle\Entity\User $marketing */
        $marketing = $userManager->createUser();

        $marketing
            ->setUsername('marketing')
            ->setPlainPassword('marketing')
            ->setFirstName('Michael')
            ->setLastName('Buckley')
            ->addRole($marketingRole)
            ->addGroup($marketingGroup)
            ->setEmail('marketing@example.com')
            ->setOrganization($organization)
            ->addOrganization($organization)
            ->setBusinessUnits(
                new ArrayCollection(
                    array(
                        $this->getBusinessUnit($manager, 'Acme, General'),
                        $this->getBusinessUnit($manager, 'Acme, East'),
                        $this->getBusinessUnit($manager, 'Acme, West')
                    )
                )
            );

        if ($this->hasReference('default_main_business')) {
            $marketing->setOwner($this->getBusinessUnit($manager, 'Acme, General'));
        }
        $this->addReference('default_marketing', $marketing);
        $userManager->updateUser($marketing);
    }

    /**
     * @param ObjectManager $manager
     * @param $name
     * @return BusinessUnit
     */
    protected function getBusinessUnit(ObjectManager $manager, $name)
    {
        return $manager->getRepository('OroOrganizationBundle:BusinessUnit')->findOneBy(['name' => $name]);
    }
}
