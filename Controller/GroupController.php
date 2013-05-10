<?php

namespace Oro\Bundle\ContactBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use YsTools\BackUrlBundle\Annotation\BackUrl;

use Oro\Bundle\UserBundle\Annotation\Acl;
use Oro\Bundle\UserBundle\Annotation\AclAncestor;

use Oro\Bundle\GridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\ContactBundle\Entity\Group;
use Oro\Bundle\ContactBundle\Datagrid\GroupContactDatagridManager;

/**
 * @Route("/group")
 * @BackUrl("back", useSession=true)
 */
class GroupController extends Controller
{
    /**
     * Create group form
     *
     * @Route("/create", name="oro_contact_group_create")
     * @Template("OroContactBundle:Group:edit.html.twig")
     * @Acl(
     *      id="oro_contact_group_create",
     *      name="Create group",
     *      description="Create new group",
     *      parent="oro_contact_group"
     * )
     */
    public function createAction()
    {
        return $this->editAction(new Group());
    }

    /**
     * Edit group form
     *
     * @Route("/edit/{id}", name="oro_contact_group_edit", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Template
     * @Acl(
     *      id="oro_contact_group_edit",
     *      name="Edit group",
     *      description="Edit group",
     *      parent="oro_contact_group"
     * )
     */
    public function editAction(Group $entity)
    {
        if ($this->get('oro_contact.form.handler.group')->process($entity)) {
            $this->get('session')->getFlashBag()->add('success', 'Group successfully saved');

            if (!$this->getRequest()->get('_widgetContainer')) {
                BackUrl::triggerRedirect();

                return $this->redirect($this->generateUrl('oro_contact_group_index'));
            }
        }

        return array(
            'datagrid' => $this->getGroupContactDatagridManager($entity)->getDatagrid()->createView(),
            'form'     => $this->get('oro_contact.form.group')->createView(),
        );
    }

    /**
     * Get grid data
     *
     * @Route(
     *      "/grid/{id}",
     *      name="oro_contact_group_contact_grid",
     *      requirements={"id"="\d+"},
     *      defaults={"id"=0, "_format"="json"}
     * )
     * @Template("OroGridBundle:Datagrid:list.json.php")
     * @AclAncestor("oro_contact_group_edit")
     */
    public function gridDataAction(Group $entity)
    {
        return array('datagrid' => $this->getGroupContactDatagridManager($entity)->getDatagrid()->createView());
    }

    /**
     * @param Group $group
     * @return GroupContactDatagridManager
     */
    protected function getGroupContactDatagridManager(Group $group)
    {
        /** @var $result GroupContactDatagridManager */
        $result = $this->get('oro_contact.group_contact.datagrid_manager');
        $result->setGroup($group);
        $result->getRouteGenerator()->setRouteParameters(array('id' => $group->getId()));
        return $result;
    }

    /**
     * @Route(
     *      "/{_format}",
     *      name="oro_contact_group_index",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @Acl(
     *      id="oro_contact_group_list",
     *      name="View contact group list",
     *      description="List of contact groups",
     *      parent="oro_contact_group"
     * )
     */
    public function indexAction(Request $request)
    {
        /** @var $datagrid DatagridInterface */
        $datagrid = $this->get('oro_contact.group.datagrid_manager')->getDatagrid();
        $view = 'json' == $request->getRequestFormat()
            ? 'OroGridBundle:Datagrid:list.json.php'
            : 'OroContactBundle:Group:index.html.twig';

        return $this->render(
            $view,
            array('datagrid' => $datagrid->createView())
        );
    }
}
