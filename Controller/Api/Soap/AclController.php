<?php

namespace Oro\Bundle\UserBundle\Controller\Api\Soap;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;
use Symfony\Component\DependencyInjection\ContainerAware;

class AclController extends ContainerAware
{

    /**
     * Get ACL Resources
     *
     * @Soap\Method("getAclIds")
     * @Soap\Result(phpType = "string[]")
     */
    public function cgetAction()
    {
        return $this->container->get('oro_user.acl_manager')->getAclResources(false);
    }

    /**
     * @Soap\Method("getAcl")
     * @Soap\Param("id", phpType = "string")
     * @Soap\Result(phpType = "Oro\Bundle\UserBundle\Entity\Acl")
     */
    public function getAction($id)
    {
        $resource = $this->container->get('oro_user.acl_manager')->getAclResource($id);
        if (!$resource) {
            throw new \SoapFault('NOT_FOUND', sprintf('Acl resource with id "%s" can not be found', $id));
        }

        return $resource;
    }
}