<?php

namespace Oro\Bundle\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

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
        $flashBag = $this->get('session')->getFlashBag();
        if ($this->getRequest()->query->has('back')) {
            $backUrl = $this->getRequest()->get('back');
            $flashBag->set('backUrl', $backUrl);
        } elseif ($flashBag->has('backUrl')) {
            $backUrl = $flashBag->get('backUrl');
            $backUrl = reset($backUrl);
        } else {
            $backUrl = null;
        }

        if ($this->get('oro_user.form.handler.role')->process($entity)) {
            $flashBag->add('success', 'Role successfully saved');

            $redirectUrl = $backUrl ? $backUrl : $this->generateUrl('oro_user_role_index');
            return $this->redirect($redirectUrl);
        }

        return array(
            'form' => $this->get('oro_user.form.role')->createView(),
        );
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
            array(
                'datagrid' => $datagrid,
                'form'     => $datagrid->getForm()->createView()
            )
        );
    }
}
