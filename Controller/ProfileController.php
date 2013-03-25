<?php

namespace Oro\Bundle\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Oro\Bundle\UserBundle\Datagrid\UserDatagridManager;

class ProfileController extends Controller
{
    /**
     * @Route("/show/{id}", name="oro_user_show", requirements={"id"="\d+"})
     * @Template
     */
    public function showAction(User $user)
    {
        return array(
            'user' => $user,
        );
    }

    /**
     * Create user form
     *
     * @Route("/create", name="oro_user_create")
     * @Template("OroUserBundle:Profile:edit.html.twig")
     */
    public function createAction()
    {
        $user = $this->getManager()->createFlexible();

        return $this->editAction($user);
    }

    /**
     * Edit user form
     *
     * @Route("/edit/{id}", name="oro_user_edit", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Template
     */
    public function editAction(User $entity)
    {
        if ($this->get('oro_user.form.handler.profile')->process($entity)) {
            $this->get('session')->getFlashBag()->add('success', 'User successfully saved');

            return $this->redirect($this->generateUrl('oro_user_index'));
        }

        return array(
            'form' => $this->get('oro_user.form.profile')->createView(),
        );
    }

   /**
    * @Route("/remove/{id}", name="oro_user_remove", requirements={"id"="\d+"})
    */
    public function removeAction(User $entity)
    {
        $this->getManager()->deleteUser($entity);
        $this->get('session')->getFlashBag()->add('success', 'User successfully removed');

        return $this->redirect($this->generateUrl('oro_user_index'));
    }

    /**
     * @Route(
     *      "/{_format}",
     *      name="oro_user_index",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     */
    public function indexAction(Request $request)
    {
        /** @var $userGridManager UserDatagridManager */
        $userGridManager = $this->get('oro_user.user_datagrid_manager');
        $datagrid = $userGridManager->getDatagrid();

        if ('json' == $request->getRequestFormat()) {
            $view = 'OroGridBundle:Datagrid:list.json.php';
        } else {
            $view = 'OroUserBundle:Profile:index.html.twig';
        }

        return $this->render(
            $view,
            array(
                'datagrid' => $datagrid,
                'form'     => $datagrid->getForm()->createView()
            )
        );
    }

    /**
     * @return UserManager
     */
    protected function getManager()
    {
        return $this->get('oro_user.manager');
    }
}
