<?php

namespace Oro\Bundle\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\UserBundle\Annotation\Acl;

/**
 * @Route("/acl")
 * @Acl(
 *      id="oro_user_acl",
 *      name="ACL manipulation",
 *      description="ACL manipulation",
 *      parent="oro_user_role"
 * )
 */
class AclController extends Controller
{
    /**
     * Show ACL resources tree for Role
     *
     * @param Role $role
     *
     * @Route("/edit/{id}", name="oro_user_acl_edit", requirements={"id"="\d+"})
     * @Template()
     * @Acl(
     *      id="oro_user_acl_edit",
     *      name="View ACL tree",
     *      description="View ACL tree for a particular role",
     *      parent="oro_user_acl"
     * )
     */
    public function editRoleAclAction(Role $role)
    {
        return array(
            'role'      => $role,
            'resources' => $this->getAclManager()->getRoleAclTree($role)
        );
    }

    /**
     * @param Role $role
     *
     * @Route("/save/{id}", name="oro_user_acl_save", requirements={"id"="\d+"})
     * @Acl(
     *      id="oro_user_acl_save",
     *      name="Modify ACL tree",
     *      description="Modify ACL tree for role",
     *      parent="oro_user_acl"
     * )
     */
    public function saveRoleAcl(Role $role)
    {
        $request = $this->getRequest();

        if ($request->isMethod('POST')) {
            $this->getAclManager()->saveRoleAcl($role, $request->request->get('resource'));
            $this->get('session')->getFlashBag()->add('success', 'ACL for role successfully saved');
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
