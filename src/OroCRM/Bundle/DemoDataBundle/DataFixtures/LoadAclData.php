<?php
namespace Oro\Bundle\TestFrameworkBundle\Tests\Performance\Demo;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManager;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadAclData extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
{
    /** @var ContainerInterface */
    private $container;

    /** @var  EntityRepository */
    protected $roles;

    /**
     * @var array
     * @return string
     */
    protected $aclTree = array(
        'oro_security',
        'oro_form_autocomplete',
        'oro_user',
        'oro_grid',
        'oro_address',
        'oro_tag',
        'orocrm_account',
        'orocrm_contact',
        'orocrm_contact_group',
        'oro_change_record_owner',
        'external_bundle_actions',
        'template_controller',
    );

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        /** @var  EntityManager $entityManager */
        $entityManager = $container->get('doctrine.orm.entity_manager');
        $this->roles = $entityManager->getRepository('OroUserBundle:Role');
    }

    public function load(ObjectManager $manager)
    {
        /** @var \Oro\Bundle\UserBundle\Entity\Role $role */
        $role = $this->roles->findOneBy(array('role' => 'ROLE_MANAGER'));

        foreach ($this->aclTree as $aclElement) {
            $acl = $manager->getRepository('Oro\Bundle\UserBundle\Entity\Acl')
                ->findOneBy(array('id' => $aclElement))
                ->addAccessRole($role);
            $manager->persist($acl);
        }
        $manager->flush();
    }

    public function getOrder()
    {
        return 100;
    }
}
