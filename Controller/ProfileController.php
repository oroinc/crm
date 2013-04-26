<?php

namespace Oro\Bundle\UserBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use YsTools\BackUrlBundle\Annotation\BackUrl;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\UserBundle\Annotation\Acl;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Entity\UserApi;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Oro\Bundle\UserBundle\Datagrid\UserDatagridManager;

/**
 * @Acl(
 *      id="oro_user_profile",
 *      name="Profile controller",
 *      description="Profile manipulation",
 *      parent="oro_user"
 * )
 * @BackUrl("back", useSession=true)
 */
class ProfileController extends Controller
{
    /**
     * @Route("/show/{id}", name="oro_user_show", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *      id="oro_user_profile_show",
     *      name="View user profile",
     *      description="View user profile",
     *      parent="oro_user_profile"
     * )
     */
    public function showAction(User $user)
    {
        return array(
            'user' => $user,
        );
    }

    /**
     * @Route("/apigen/{id}", name="oro_user_apigen", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *      id="oro_user_profile_apigen",
     *      name="Generate new API key",
     *      description="Generate new API key",
     *      parent="oro_user_profile"
     * )
     */
    public function apigenAction(User $user)
    {
        if (!$api = $user->getApi()) {
            $api = new UserApi();
        }

        $api->setApiKey($api->generateKey())
            ->setUser($user);

        $em = $this->getDoctrine()->getManager();

        $em->persist($api);
        $em->flush();

        return $this->getRequest()->isXmlHttpRequest()
            ? new JsonResponse($api->getApiKey())
            : $this->forward('OroUserBundle:Profile:show', array('user' => $user));
    }

    /**
     * Create user form
     *
     * @Route("/create", name="oro_user_create")
     * @Template("OroUserBundle:Profile:edit.html.twig")
     * @Acl(
     *      id="oro_user_profile_create",
     *      name="Create user profile",
     *      description="Create user profile",
     *      parent="oro_user_profile"
     * )
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
     * @Acl(
     *      id="oro_user_profile_edit",
     *      name="Edit user profile",
     *      description="Edit user profile",
     *      parent="oro_user_profile"
     * )
     */
    public function editAction(User $entity)
    {
        if ($this->get('oro_user.form.handler.profile')->process($entity)) {
            $this->get('session')->getFlashBag()->add('success', 'User successfully saved');

            BackUrl::triggerRedirect();

            return $this->redirect($this->generateUrl('oro_user_index'));
        }

        return array(
            'form' => $this->get('oro_user.form.profile')->createView(),
        );
    }

    /**
     * @Route(
     *      "/{_format}",
     *      name="oro_user_index",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @Acl(
     *      id="oro_user_profile_list",
     *      name="View list of user profiles",
     *      description="View list of user profiles",
     *      parent="oro_user_profile"
     * )
     */
    public function indexAction(Request $request)
    {
        $datagrid = $this->get('oro_user.user_datagrid_manager')->getDatagrid();
        $view     = 'json' == $request->getRequestFormat()
            ? 'OroGridBundle:Datagrid:list.json.php'
            : 'OroUserBundle:Profile:index.html.twig';

        return $this->render(
            $view,
            array('datagrid' => $datagrid->createView())
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
