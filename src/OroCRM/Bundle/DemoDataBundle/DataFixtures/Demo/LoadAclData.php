<?php
namespace OroCRM\Bundle\DemoDataBundle\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\SecurityBundle\Acl\Extension\EntityMaskBuilder;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadAclData extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
{
    /** @var ContainerInterface */
    private $container;

    /** @var  AclManager */
    protected $aclmanager;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->aclmanager = $container->get('oro_security.acl.manager');

    }

    public function load(ObjectManager $manager)
    {
        $sid = $this->aclmanager->getSid('ROLE_MANAGER');

        $this->addAcls(
            $sid,
            array(
                'Entity:OroCRMSalesBundle:Opportunity' =>
                    array('CREATE_SYSTEM', 'VIEW_SYSTEM', 'EDIT_SYSTEM', 'DELETE_SYSTEM'),
                'Entity:OroCRMSalesBundle:Lead' =>
                    array('CREATE_SYSTEM', 'VIEW_SYSTEM', 'EDIT_SYSTEM', 'DELETE_SYSTEM'),
                'Entity:OroCRMContactBundle:Group' =>
                    array('CREATE_SYSTEM', 'VIEW_SYSTEM', 'EDIT_SYSTEM', 'DELETE_SYSTEM', 'ASSIGN_SYSTEM'),
                'Entity:OroCRMContactBundle:Contact' =>
                    array('CREATE_SYSTEM', 'VIEW_SYSTEM', 'EDIT_SYSTEM', 'DELETE_SYSTEM', 'ASSIGN_SYSTEM'),
                'Entity:OroCRMAccountBundle:Account' =>
                    array('CREATE_SYSTEM', 'VIEW_SYSTEM', 'EDIT_SYSTEM', 'DELETE_SYSTEM', 'ASSIGN_SYSTEM'),
                'Entity:OroTagBundle:Tag' =>
                    array('CREATE_SYSTEM', 'VIEW_SYSTEM', 'EDIT_SYSTEM', 'DELETE_SYSTEM', 'ASSIGN_SYSTEM'),
            )
        );
        $this->resetAcl(
            $sid,
            array(
                'Entity:OroOrganizationBundle:BusinessUnit',
                'Entity:OroNotificationBundle:EmailNotification',
                'Entity:OroEmailBundle:EmailTemplate',
                'Entity:OroUserBundle:Group',
                'Entity:OroUserBundle:Role',
                'Entity:OroUserBundle:User',
                'Action:oro_jobs',
                'Action:oro_email_view',
                'Action:oro_dataaudit_history',
                'Action:oro_config_system',
                'Action:oro_entityconfig_manage'
            )
        );

        $this->aclmanager->flush();
    }

    /**
     * @param $sid
     * @param array $entities
     */
    protected function resetAcl($sid, array $entities)
    {
        foreach ($entities as $entity) {
            $oid = $this->aclmanager->getOid($entity);
            /** @var EntityMaskBuilder $builder */
            $builder = $this->aclmanager->getMaskBuilder($oid);
            $mask = $builder
                ->reset()
                ->get();
            $this->aclmanager->setPermission(
                $sid,
                $oid,
                $mask
            );
        }
    }

    /**
     * @param $sid
     * @param array $entities
     */
    protected function addAcls($sid, array $entities)
    {
        foreach ($entities as $entity => $acls) {
            $oid = $this->aclmanager->getOid($entity);
            $builder = $this->aclmanager->getMaskBuilder($oid);
            $mask = $builder->reset()->get();
            foreach ($acls as $acl) {
                $mask = $builder
                    ->add($acl)
                    ->get();
            }
            $this->aclmanager->setPermission(
                $sid,
                $oid,
                $mask
            );
        }
    }

    public function getOrder()
    {
        return 100;
    }
}
