<?php

namespace Oro\Bundle\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use YsTools\BackUrlBundle\Annotation\BackUrl;

use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\UserBundle\Annotation\Acl;
use Oro\Bundle\UserBundle\Datagrid\RoleDatagridManager;

/**
 * @Route("/role")
 * @Acl(
 *      id = "oro_role",
 *      name="Role controller",
 *      description = "Role manipulation"
 * )
 * @BackUrl("back", useSession=true)
 */
class RoleController extends Controller
{
    /**
     * Create role form
     *
     * @Route("/create", name="oro_user_role_create")
     * @Template("OroUserBundle:Role:edit.html.twig")
     */
    public function createAction()
    {
        return $this->editAction(new Role());
    }

    /**
     * Edit role form
     *
     * @Route("/edit/{id}", name="oro_user_role_edit", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Template
     */
    public function editAction(Role $entity)
    {
        if ($this->get('oro_user.form.handler.role')->process($entity)) {
            $this->get('session')->getFlashBag()->add('success', 'Role successfully saved');
            BackUrl::triggerRedirect();
            return $this->redirect($this->generateUrl('oro_user_role_index'));
        }

        /** @var $userGridManager RoleDatagridManager */
        $userGridManager = $this->get('oro_user.role_user_datagrid_manager');
        $userGridManager->getRouteGenerator()->setRouteParameters(array('id' => $entity->getId()));
        $datagrid = $userGridManager->getDatagrid();

        return array(
            'datagrid' => $datagrid->createView(),
            'form' => $this->get('oro_user.form.role')->createView(),
        );
    }

    /**
     * Get grid users data
     *
     * @Route(
     *  "/grid/{id}",
     *  name="oro_user_role_user_grid",
     *  requirements={"id"="\d+"},
     *  defaults={"id"=0, "_format"="json"}
     * )
     * @Template("OroGridBundle:Datagrid:list.json.php")
     */
    public function gridDataAction(Role $entity)
    {
        $this->get('oro_user.role_user_datagrid_manager.default_query_factory')
             ->setQueryBuilder(
                 $this->get('oro_user.role_manager')->getUserQueryBuilder($entity)
             );

        /** @var $datagridManager RoleDatagridManager */
        $datagridManager = $this->get('oro_user.role_user_datagrid_manager');
        $datagrid = $datagridManager->getDatagrid();

        return array('datagrid' => $datagrid->createView());
    }

    /**
     * @Route("/remove/{id}", name="oro_user_role_remove", requirements={"id"="\d+"})
     */
    public function removeAction(Role $entity)
    {
        $em = $this->getDoctrine()->getManager();

        $em->remove($entity);
        $em->flush();

        $this->get('session')->getFlashBag()->add('success', 'Role successfully removed');

        return $this->redirect($this->generateUrl('oro_user_role_index'));
    }

    /**
     * @Route(
     *      "/{_format}",
     *      name="oro_user_role_index",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @Acl(
     *      id = "oro_role_list",
     *      name="Role list",
     *      description = "List of roles",
     *      parent = "oro_role"
     * )
     */
    public function indexAction(Request $request)
    {
        /** @var $roleGridManager RoleDatagridManager */
        $roleGridManager = $this->get('oro_user.role_datagrid_manager');
        $datagrid = $roleGridManager->getDatagrid();

        if ('json' == $request->getRequestFormat()) {
            $view = 'OroGridBundle:Datagrid:list.json.php';
        } else {
            $view = 'OroUserBundle:Role:index.html.twig';
        }

        return $this->render(
            $view,
            array('datagrid' => $datagrid->createView())
        );
    }
}
