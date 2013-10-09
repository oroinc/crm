<?php
namespace OroCRM\Bundle\DemoDataBundle\DataFixtures;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManager;

use Oro\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository;
use Oro\Bundle\TagBundle\Entity\TagManager;
use OroCRM\Bundle\DemoDataBundle\DataFixtures\AbstractFlexibleFixture;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\SecurityContext;

class LoadUserData extends AbstractFlexibleFixture implements ContainerAwareInterface, OrderedFixtureInterface
{
    /** @var ContainerInterface */
    private $container;

    /** @var  EntityRepository */
    protected $roles;

    /** @var  EntityRepository */
    protected $group;

    /** @var FlexibleEntityRepository */
    protected $user;

    /** @var  TagManager */
    protected $tagManager;

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
        /** @var \Oro\Bundle\UserBundle\Entity\Role $marketingRole */
        $marketingRole = $this->roles->findOneBy(array('role' => 'ROLE_MANAGER'));
        /** @var \Oro\Bundle\UserBundle\Entity\Role $saleRole */
        $saleRole = $this->roles->findOneBy(array('role' => 'ROLE_MANAGER'));
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
            ->setFirstname('Ellen')
            ->setLastname('Rowell')
            ->addRole($saleRole)
            ->addGroup($salesGroup)
            ->setEmail('sale@example.com')
            ->setBusinessUnits(
                new ArrayCollection(
                    array(
                        $this->getReference('default_main_business'),
                        $this->getReference('default_core_business'),
                        $this->getReference('default_crm_business')
                    )
                )
            );
        $this->setFlexibleAttributeValue($userManager, $sale, 'company', 'Oro, Inc');
        $this->setFlexibleAttributeValueOption($userManager, $sale, 'gender', 'Female');

        if ($this->hasReference('default_main_business')) {
            $sale->setOwner($this->getReference('default_main_business'));
        }
        $this->addReference('default_sale', $sale);
        $userManager->updateUser($sale);

        /** @var \Oro\Bundle\UserBundle\Entity\User $marketing */
        $marketing = $userManager->createUser();

        $marketing
            ->setUsername('marketing')
            ->setPlainPassword('marketing')
            ->setFirstname('Michael')
            ->setLastname('Buckley')
            ->addRole($marketingRole)
            ->addGroup($marketingGroup)
            ->setEmail('marketing@example.com')
            ->setBusinessUnits(
                new ArrayCollection(
                    array(
                        $this->getReference('default_main_business'),
                        $this->getReference('default_core_business'),
                        $this->getReference('default_crm_business')
                    )
                )
            );
        $this->setFlexibleAttributeValue($userManager, $marketing, 'company', 'Oro, Inc');
        $this->setFlexibleAttributeValueOption($userManager, $marketing, 'gender', 'Male');

        if ($this->hasReference('default_main_business')) {
            $marketing->setOwner($this->getReference('default_main_business'));
        }
        $this->addReference('default_marketing', $marketing);
        $userManager->updateUser($marketing);
    }

    public function getOrder()
    {
        return 110;
    }
}
