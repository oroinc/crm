<?php

namespace Oro\Bundle\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/acl")
 */
class AclController extends Controller
{
    /**
     * Show ACL Resources tree
     *
     * @Route("/list", name="oro_user_acl_list")
     * @Template()
     */
    public function getAclResourcesLIstAction()
    {
        return array(
            'resources' => $this->get('oro_user.acl_reader')->getResourcesTree()
        );
    }
}
