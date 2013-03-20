<?php

namespace Oro\Bundle\UserBundle\Controller\Api\Rest;

use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\View\RouteRedirectView;
use FOS\Rest\Util\Codes;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Oro\Bundle\UserBundle\Entity\User;

/**
 * @NamePrefix("oro_api_")
 */
class ProfileController extends FOSRestController implements ClassResourceInterface
{
    /**
     * Get the list of users
     *
     * @QueryParam(name="page", requirements="\d+", nullable=true, description="Page number, starting from 1. Defaults to 1.")
     * @QueryParam(name="limit", requirements="\d+", nullable=true, description="Number of items per page. defaults to 10.")
     * @ApiDoc(
     *  description="Get the list of users",
     *  resource=true,
     *  filters={
     *      {"name"="page", "dataType"="integer"},
     *      {"name"="limit", "dataType"="integer"}
     *  }
     * )
     */
    public function cgetAction()
    {
        $pager = $this->get('knp_paginator')->paginate(
            $this->getManager()
                ->getListQuery()
                ->getQuery()
                ->setHydrationMode(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY),
            (int) $this->getRequest()->get('page', 1),
            (int) $this->getRequest()->get('limit', 10)
        );

        return $this->handleView($this->view(
            $pager->getItems(),
            Codes::HTTP_OK
        ));
    }

    /**
     * Get user data
     *
     * @param int $id User id
     * @ApiDoc(
     *  description="Get user data",
     *  resource=true,
     *  requirements={
     *      {"name"="id", "dataType"="integer"},
     *  }
     * )
     */
    public function getAction($id)
    {
        $entity = $this->getManager()->findUserBy(array('id' => (int) $id));

        return $this->handleView($this->view(
            $entity,
            $entity ? Codes::HTTP_OK : Codes::HTTP_NOT_FOUND
        ));
    }

    /**
     * Create new user
     *
     * @ApiDoc(
     *  description="Create new user",
     *  resource=true
     * )
     */
    public function postAction()
    {
        $entity = $this->getManager()->createFlexible();

        $this->fixFlexRequest($entity);

        $view = $this->get('oro_user.form.handler.profile.api')->process($entity)
            ? RouteRedirectView::create('oro_api_get_profile', array('id' => $entity->getId()), Codes::HTTP_CREATED)
            : $this->view($this->get('oro_user.form.profile.api'), Codes::HTTP_BAD_REQUEST);

        return $this->handleView($view);
    }

    /**
     * Update existing user
     *
     * @param int $id User id
     * @ApiDoc(
     *  description="Update existing user",
     *  resource=true,
     *  requirements={
     *      {"name"="id", "dataType"="integer"},
     *  }
     * )
     */
    public function putAction($id)
    {
        /* @var $entity \Oro\Bundle\UserBundle\Entity\User */
        $entity = $this->getManager()->findUserBy(array('id' => (int) $id));

        if (!$entity) {
            return $this->handleView($this->view('', Codes::HTTP_NOT_FOUND));
        }

        $this->fixFlexRequest($entity);

        $view = $this->get('oro_user.form.handler.profile.api')->process($entity)
            ? RouteRedirectView::create('oro_api_get_profile', array('id' => $entity->getId()), Codes::HTTP_NO_CONTENT)
            : $this->view($this->get('oro_user.form.profile.api'), Codes::HTTP_BAD_REQUEST);

        return $this->handleView($view);
    }

    /**
     * Delete user
     *
     * @param int $id User id
     * @ApiDoc(
     *  description="Delete user",
     *  resource=true,
     *  requirements={
     *      {"name"="id", "dataType"="integer"},
     *  }
     * )
     */
    public function deleteAction($id)
    {
        $entity = $this->getManager()->findUserBy(array('id' => (int) $id));

        if (!$entity) {
            return $this->handleView($this->view('', Codes::HTTP_NOT_FOUND));
        }

        $this->getManager()->deleteUser($entity);

        return $this->handleView($this->view('', Codes::HTTP_NO_CONTENT));
    }

    /**
     * Get user roles
     *
     * @param int $id User id
     * @ApiDoc(
     *  description="Get user roles",
     *  resource=true,
     *  requirements={
     *      {"name"="id", "dataType"="integer"},
     *  }
     * )
     */
    public function getRolesAction($id)
    {
        $entity = $this->getManager()->findUserBy(array('id' => (int) $id));

        if (!$entity) {
            return $this->handleView($this->view('', Codes::HTTP_NOT_FOUND));
        }

        return $this->handleView($this->view($entity->getRoles(), Codes::HTTP_OK));
    }

    /**
     * Get user groups
     *
     * @param int $id User id
     * @ApiDoc(
     *  description="Get user groups",
     *  resource=true,
     *  requirements={
     *      {"name"="id", "dataType"="integer"},
     *  }
     * )
     */
    public function getGroupsAction($id)
    {
        $entity = $this->getManager()->findUserBy(array('id' => (int) $id));

        if (!$entity) {
            return $this->handleView($this->view('', Codes::HTTP_NOT_FOUND));
        }

        return $this->handleView($this->view($entity->getGroups(), Codes::HTTP_OK));
    }

    /**
     * Get user acl list
     *
     * @param int $id User id
     * @ApiDoc(
     *  description="Get user allowed ACL resources",
     *  resource=true,
     *  requirements={
     *      {"name"="id", "dataType"="integer"},
     *  }
     * )
     */
    public function getAclAction($id)
    {
        $user = ($this->getManager()->findUserBy(array('id' => (int) $id)));
        if (!$user) {
            return $this->handleView($this->view('', Codes::HTTP_NOT_FOUND));
        }

        return $this->handleView($this->view($this->getAclManager()->getAclForUser($user), Codes::HTTP_OK));
    }

    /**
     * Filter user by username or email
     *
     * @QueryParam(name="email", requirements="[a-zA-Z0-9\-_\.@]+", nullable=true, description="Email to filter")
     * @QueryParam(name="username", requirements="[a-zA-Z0-9\-_\.]+", nullable=true, description="Username to filter")
     * @ApiDoc(
     *  description="Get user by username or email",
     *  resource=true,
     *  filters={
     *      {"name"="email", "dataType"="string"},
     *      {"name"="username", "dataType"="string"}
     *  }
     * )
     */
    public function getFilterAction()
    {
        $params = $this->getRequest()->query->all();

        if (empty($params)) {
            return $this->handleView($this->view('', Codes::HTTP_BAD_REQUEST));
        }

        $entity = $this->getManager()->findUserBy($params);

        return $this->handleView($this->view(
            $entity,
            $entity ? Codes::HTTP_OK : Codes::HTTP_NOT_FOUND
        ));
    }

    /**
     * @return \Oro\Bundle\UserBundle\Acl\Manager
     */
    protected function getAclManager()
    {
        return $this->get('oro_user.acl_manager');
    }

    /**
     * @return \Oro\Bundle\UserBundle\Entity\UserManager
     */
    protected function getManager()
    {
        return $this->get('oro_user.manager');
    }

    /**
     * This is temporary fix for flexible entity values processing.
     *
     * Assumed that user will post data in the following format:
     * {"profile":{"username":"john123","email":"john@doe.com","attributes":{"firstname":"John"}}}
     *
     * @param User $user
     */
    protected function fixFlexRequest(User $entity)
    {
        $request = $this->getRequest()->request;
        $data    = $request->get('profile', array());
        $attrDef = $this->getManager()->getAttributeRepository()->findBy(array('entityType' => get_class($entity)));
        $attrVal = isset($data['attributes']) ? $data['attributes'] : array();

        $data['attributes'] = array();

        foreach ($attrDef as $i => $attr) {
            /* @var $attr \Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityAttribute */
            if ($attr->getBackendType() == 'options') {
               if (in_array(
                    $attr->getAttributeType(),
                    array(
                        'Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\OptionMultiSelectType',
                        'Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\OptionMultiCheckboxType',
                    ))
                ) {
                    $type    = 'options';
                    $default = array($attr->getOptions()->offsetGet(0)->getId());
                } else {
                    $type    = 'option';
                    $default = $attr->getOptions()->offsetGet(0)->getId();
                }
            } else {
                $type    = 'data';
                $default = null;
            }

            $data['attributes'][$i]        = array();
            $data['attributes'][$i]['id']  = $attr->getId();
            $data['attributes'][$i][$type] = $default;

            foreach ($attrVal as $field) {
                if ($attr->getCode() == (string) $field->code) {
                    $data['attributes'][$i][$type] = (string) $field->value;

                    break;
                }
            }
        }

        $request->set('profile', $data);
    }
}
