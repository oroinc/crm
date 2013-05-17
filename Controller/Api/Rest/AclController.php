<?php

namespace Oro\Bundle\UserBundle\Controller\Api\Rest;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\Rest\Util\Codes;
use FOS\RestBundle\Controller\Annotations\QueryParam;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Oro\Bundle\UserBundle\Annotation\Acl;
use Oro\Bundle\UserBundle\Annotation\AclAncestor;

/**
 * @NamePrefix("oro_api_")
 * @Acl(
 *      id="oro_user_acl",
 *      name="ACL manipulation",
 *      description="ACL manipulation from API",
 *      parent="oro_user_role"
 * )
 */
class AclController extends FOSRestController implements ClassResourceInterface
{
    /**
     * Get ACL resources
     *
     * @ApiDoc(
     *      description="Get ACL resources",
     *      resource=true
     * )
     * @Acl(
     *      id="oro_user_acl_edit",
     *      name="View ACL tree",
     *      description="View ACL tree for a particular role",
     *      parent="oro_user_acl"
     * )
     */
    public function cgetAction()
    {
        return $this->handleView(
            $this->view(
                $this->get('oro_user.acl_manager')->getAclResources(false),
                Codes::HTTP_OK
            )
        );
    }

    /**
     * Get ACL resource data
     *
     * @param string $id ACL resource id
     *
     * @QueryParam(name="id", nullable=false, description="ACL resource id")
     * @ApiDoc(
     *      description="Get ACL resource data",
     *      resource=true,
     *      filters={
     *          {"name"="id", "dataType"="string"},
     *      }
     * )
     * @Acl(
     *      id="oro_user_acl_show",
     *      name="View ACL resource",
     *      description="View ACL resource",
     *      parent="oro_user_acl"
     * )
     */
    public function getAction($id)
    {
        $resource = $this->get('oro_user.acl_manager')->getAclResource($id);

        if (!$resource) {
            return $this->handleView($this->view('', Codes::HTTP_NOT_FOUND));
        }

        return $this->handleView(
            $this->view(
                $resource->toArray(),
                $resource ? Codes::HTTP_OK : Codes::HTTP_NOT_FOUND
            )
        );
    }
}
