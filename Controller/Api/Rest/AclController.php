<?php

namespace Oro\Bundle\UserBundle\Controller\Api\Rest;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\Rest\Util\Codes;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 *@NamePrefix("oro_api_")
 */
class AclController extends FOSRestController implements ClassResourceInterface
{

    /**
     * Get ACL Resources
     *
     * @param int $id Group id
     * @ApiDoc(
     *  description="Get ACL Resources ",
     *  resource=true
     * )
     */
    public function cgetAction()
    {
        return $this->handleView($this->view(
                $this->get('oro_user.acl_manager')->getAclResources(false),
                Codes::HTTP_OK
            )
        );
    }

    /**
     * Get ACL Resource data
     *
     * @param int $id Group id
     * @ApiDoc(
     *  description="Get Acl resource",
     *  resource=true,
     *  filters={
     *      {"name"="id", "dataType"="string"},
     *  }
     * )
     */
    public function getAction($id)
    {
        $resource = $this->get('oro_user.acl_manager')->getAclResource($id);

        if (!$resource) {
            return $this->handleView($this->view('', Codes::HTTP_NOT_FOUND));
        }

        return $this->handleView($this->view(
                $resource->toArray(),
                $resource ? Codes::HTTP_OK : Codes::HTTP_NOT_FOUND
            )
        );
    }
}