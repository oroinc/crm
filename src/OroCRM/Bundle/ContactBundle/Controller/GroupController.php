<?php

namespace OroCRM\Bundle\ContactBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use Oro\Bundle\GridBundle\Datagrid\DatagridInterface;
use OroCRM\Bundle\ContactBundle\Entity\Group;
use OroCRM\Bundle\ContactBundle\Datagrid\GroupContactDatagridManager;

/**
 * @Route("/group")
 */
class GroupController extends Controller
{
    /**
     * Create group form
     *
     * @Route("/create", name="orocrm_contact_group_create")
     * @Template("OroCRMContactBundle:Group:update.html.twig")
     * @Acl(
     *      id="orocrm_contact_group_create",
     *      type="entity",
     *      permission="CREATE",
     *      class="OroCRMContactBundle:Group"
     * )
     */
    public function createAction()
    {
        return $this->update(new Group());
    }

    /**
     * Update group form
     *
     * @Route("/update/{id}", name="orocrm_contact_group_update", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Template
     * @Acl(
     *      id="orocrm_contact_group_update",
     *      type="entity",
     *      permission="EDIT",
     *      class="OroCRMContactBundle:Group"
     * )
     */
    public function updateAction(Group $entity)
    {
        return $this->update($entity);
    }

    /**
     * Get grid data
     *
     * @Route(
     *      "/grid/{id}",
     *      name="orocrm_contact_group_contact_grid",
     *      requirements={"id"="\d+"},
     *      defaults={"id"=0, "_format"="json"}
     * )
     * @Template("OroGridBundle:Datagrid:list.json.php")
     * @AclAncestor("orocrm_contact_view")
     */
    public function gridDataAction(Group $entity = null)
    {
        if (!$entity) {
            $entity = new Group();
        }

        return array('datagrid' => $this->getGroupContactDatagridManager($entity)->getDatagrid()->createView());
    }

    /**
     * @param Group $group
     * @return GroupContactDatagridManager
     */
    protected function getGroupContactDatagridManager(Group $group)
    {
        /** @var $result GroupContactDatagridManager */
        $result = $this->get('orocrm_contact.group_contact.datagrid_manager');
        $result->setGroup($group);
        $result->getRouteGenerator()->setRouteParameters(array('id' => $group->getId()));
        return $result;
    }

    /**
     * @Route(
     *      "/{_format}",
     *      name="orocrm_contact_group_index",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @Acl(
     *      id="orocrm_contact_group_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="OroCRMContactBundle:Group"
     * )
     */
    public function indexAction(Request $request)
    {
        /** @var $datagrid DatagridInterface */
        $datagrid = $this->get('orocrm_contact.group.datagrid_manager')->getDatagrid();
        $view = 'json' == $request->getRequestFormat()
            ? 'OroGridBundle:Datagrid:list.json.php'
            : 'OroCRMContactBundle:Group:index.html.twig';

        return $this->render(
            $view,
            array('datagrid' => $datagrid->createView())
        );
    }

    /**
     * @param Group $entity
     * @return array
     */
    protected function update(Group $entity)
    {
        if ($this->get('orocrm_contact.form.handler.group')->process($entity)) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('orocrm.contact.controller.contact_group.saved.message')
            );

            if (!$this->getRequest()->get('_widgetContainer')) {

                return $this->get('oro_ui.router')->actionRedirect(
                    array(
                        'route' => 'orocrm_contact_group_update',
                        'parameters' => array('id' => $entity->getId()),
                    ),
                    array(
                        'route' => 'orocrm_contact_group_index',
                    )
                );
            }
        }

        return array(
            'datagrid' => $this->getGroupContactDatagridManager($entity)->getDatagrid()->createView(),
            'form'     => $this->get('orocrm_contact.form.group')->createView(),
            'showContactsGrid' => count($this->get('orocrm_contact.contact.manager')->getList()) ? true : false
        );
    }
}
