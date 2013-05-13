<?php

namespace Oro\Bundle\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use YsTools\BackUrlBundle\Annotation\BackUrl;

use Oro\Bundle\UserBundle\Entity\Group;
use Oro\Bundle\UserBundle\Annotation\Acl;
use Oro\Bundle\UserBundle\Annotation\AclAncestor;
use Oro\Bundle\UserBundle\Datagrid\GroupDatagridManager;

/**
 * @Route("/group")
 * @Acl(
 *      id="oro_user_group",
 *      name="Group manipulation",
 *      description="Group manipulation"
 * )
 * @BackUrl("back", useSession=true)
 */
class GroupController extends Controller
{
    /**
     * Create group form
     *
     * @Route("/create", name="oro_user_group_create")
     * @Template("OroUserBundle:Group:edit.html.twig")
     * @Acl(
     *      id="oro_user_group_create",
     *      name="Create group",
     *      description="Create new group",
     *      parent="oro_user_group"
     * )
     */
    public function createAction()
    {
        return $this->editAction(new Group());
    }

    /**
     * Edit group form
     *
     * @Route("/edit/{id}", name="oro_user_group_edit", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Template
     * @Acl(
     *      id="oro_user_group_edit",
     *      name="Edit group",
     *      description="Edit group",
     *      parent="oro_user_group"
     * )
     */
    public function editAction(Group $entity)
    {
        if ($this->get('oro_user.form.handler.group')->process($entity)) {
            $this->get('session')->getFlashBag()->add('success', 'Group successfully saved');

            if (!$this->getRequest()->get('_widgetContainer')) {
                BackUrl::triggerRedirect();

                return $this->redirect($this->generateUrl('oro_user_group_index'));
            }
        }

        /** @var $gridManager GroupDatagridManager */
        $gridManager = $this->get('oro_user.group_user_datagrid_manager');

        $this->initializeQueryFactory($entity);
        $gridManager->getRouteGenerator()->setRouteParameters(array('id' => $entity->getId()));

        $datagrid = $gridManager->getDatagrid();

        return array(
            'datagrid' => $datagrid->createView(),
            'form'     => $this->get('oro_user.form.group')->createView(),
        );
    }

    /**
     * Get grid data
     *
     * @Route(
     *      "/grid/{id}",
     *      name="oro_user_group_user_grid",
     *      requirements={"id"="\d+"},
     *      defaults={"id"=0, "_format"="json"}
     * )
     * @Template("OroGridBundle:Datagrid:list.json.php")
     * @AclAncestor("oro_user_group_list")
     */
    public function gridDataAction(Group $entity)
    {
        $this->initializeQueryFactory($entity);

        $datagrid = $this->get('oro_user.group_user_datagrid_manager')->getDatagrid();

        return array('datagrid' => $datagrid->createView());
    }

    /**
     * @Route(
     *      "/{_format}",
     *      name="oro_user_group_index",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @Acl(
     *      id="oro_user_group_list",
     *      name="View group list",
     *      description="List of groups",
     *      parent="oro_user_group"
     * )
     */
    public function indexAction(Request $request)
    {
        $datagrid = $this->get('oro_user.group_datagrid_manager')->getDatagrid();
        $view     = 'json' == $request->getRequestFormat()
            ? 'OroGridBundle:Datagrid:list.json.php'
            : 'OroUserBundle:Group:index.html.twig';

        return $this->render(
            $view,
            array('datagrid' => $datagrid->createView())
        );
    }

    /**
     * @param Group $entity
     */
    protected function initializeQueryFactory(Group $entity)
    {
        $this->get('oro_user.group_user_datagrid_manager.default_query_factory')
            ->setQueryBuilder(
                $this->get('oro_user.group_manager')->getUserQueryBuilder($entity)
            );
    }
}
