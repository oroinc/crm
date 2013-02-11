<?php

namespace Oro\Bundle\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\UserBundle\Entity\Role;

/**
 * @Route("/acl")
 */
class AclController extends Controller
{
    /**
     * Show ACL Resources tree for Role
     *
     * @Route("/edit/{id}", name="oro_user_acl_edit", requirements={"id"="\d+"})
     * @param \Oro\Bundle\UserBundle\Entity\Role $role
     * @Template()
     * @return array
     */
    public function editRoleAclAction(Role $role)
    {
        return array(
            'role' => $role,
            'resources' => $this->getAclManager()->getRoleAclTree($role)
        );
    }

    /**
     * @Route("/save/{id}", name="oro_user_acl_save", requirements={"id"="\d+"})
     * @param \Oro\Bundle\UserBundle\Entity\Role $role
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function saveRoleAcl(Role $role)
    {
        $request = $this->getRequest();
        if ($request->getMethod() == 'POST') {
            $this->getAclManager()->saveRoleAcl($role, $request->request->get('resource'));
            $this->get('session')->getFlashBag()->add('success', 'Role ACL successfully saved');
        }

        return $this->redirect($this->generateUrl('oro_user_role_index'));
    }

    /**
     * @return \Oro\Bundle\UserBundle\Acl\Manager
     */
    protected function getAclManager()
    {
        return $this->get('oro_user.acl_manager');
    }
}
