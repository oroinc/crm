<?php

namespace Oro\Bundle\NavigationBundle\Controller\Api;

use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\Rest\Util\Codes;
use Oro\Bundle\UserBundle\Entity\User;

use Oro\Bundle\NavigationBundle\Entity\Builder\ItemFactory;
use Oro\Bundle\NavigationBundle\Entity\Repository\NavigationRepositoryInterface;

/**
 * @RouteResource("navigationitems")
 * @NamePrefix("oro_api_")
 */
class NavigationItemController extends FOSRestController
{
    /**
     * REST GET list
     *
     * @param string $type
     *
     * @ApiDoc(
     *  description="Get all Navigation items for user",
     *  resource=true
     * )
     * @return Response
     */
    public function getAction($type)
    {
        /** @var $entity \Oro\Bundle\NavigationBundle\Entity\NavigationItemInterface */
        $entity = $this->getFactory()->createItem($type, array());

        /** @var $repo NavigationRepositoryInterface */
        $repo = $this->getDoctrine()->getRepository(get_class($entity));
        $items = $repo->getNavigationItems($this->getUserId(), $type);

        return $this->handleView(
            $this->view($items, is_array($items) ? Codes::HTTP_OK : Codes::HTTP_NOT_FOUND)
        );
    }

    /**
     * REST POST
     *
     * @param string $type
     *
     * @ApiDoc(
     *  description="Add Navigation item",
     *  resource=true
     * )
     * @return Response
     */
    public function postAction($type)
    {
        $postArray = $this->getRequest()->request->all();
        if (empty($postArray) || empty($postArray['type'])) {
            return $this->handleView(
                $this->view(
                    array('message' => 'Wrong JSON inside POST body'),
                    Codes::HTTP_BAD_REQUEST
                )
            );
        }

        /** @var $user \Oro\Bundle\UserBundle\Entity\User */
        $user = $this->getDoctrine()
            ->getRepository('OroUserBundle:User')
            ->find($this->getUserId());
        $postArray['user'] = $user;

        /** @var $entity \Oro\Bundle\NavigationBundle\Entity\NavigationItemInterface */
        $entity = $this->getFactory()->createItem($type, $postArray);
        if (!$entity) {
            return $this->handleView($this->view(array(), Codes::HTTP_NOT_FOUND));
        }

        $manager = $this->getManager();
        $manager->persist($entity);
        $manager->flush();

        return $this->handleView(
            $this->view(array('id' => $entity->getId()), Codes::HTTP_CREATED)
        );
    }

    /**
     * REST PUT
     *
     * @param string $type
     * @param int $itemId Navigation item id
     *
     * @ApiDoc(
     *  description="Update Navigation item",
     *  resource=true
     * )
     * @return Response
     */
    public function putIdAction($type, $itemId)
    {
        $postArray = $this->getRequest()->request->all();
        if (empty($postArray)) {
            return $this->handleView(
                $this->view(
                    array('message' => 'Wrong JSON inside POST body'),
                    Codes::HTTP_BAD_REQUEST
                )
            );
        }

        /** @var $entity \Oro\Bundle\NavigationBundle\Entity\NavigationItemInterface */
        $entity = $this->getFactory()->findItem($type, (int)$itemId);
        if (!$entity) {
            return $this->handleView($this->view(array(), Codes::HTTP_NOT_FOUND));
        }
        if (!$this->validatePermissions($entity->getUser())) {
            return $this->handleView($this->view(array(), Codes::HTTP_FORBIDDEN));
        }

        $entity->setValues($postArray);

        $em = $this->getManager();
        $em->persist($entity);
        $em->flush();

        return $this->handleView($this->view(array(), Codes::HTTP_OK));
    }

    /**
     * REST DELETE
     *
     * @param string $type
     * @param int $itemId
     *
     * @ApiDoc(
     *  description="Remove Navigation item",
     *  resource=true
     * )
     * @return Response
     */
    public function deleteIdAction($type, $itemId)
    {
        /** @var $entity \Oro\Bundle\NavigationBundle\Entity\NavigationItemInterface */
        $entity = $this->getFactory()->findItem($type, (int)$itemId);
        if (!$entity) {
            return $this->handleView($this->view(array(), Codes::HTTP_NOT_FOUND));
        }
        if (!$this->validatePermissions($entity->getUser())) {
            return $this->handleView($this->view(array(), Codes::HTTP_FORBIDDEN));
        }

        $em = $this->getManager();
        $em->remove($entity);
        $em->flush();

        return $this->handleView($this->view(array(), Codes::HTTP_NO_CONTENT));
    }

    /**
     * Get current user id
     *
     * @return int
     */
    protected function getUserId()
    {
        /** @var $user User */
        $user = $this->getUser();
        return $user ? $user->getId() : 0;
    }

    /**
     * Validate permissions on pinbar
     *
     * @param User $user
     * @return bool
     */
    protected function validatePermissions(User $user)
    {
        return $user->getId() == $this->getUserId();
    }

    /**
     * Get entity Manager
     *
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    protected function getManager()
    {
        return $this->getDoctrine()->getEntityManagerForClass('OroNavigationBundle:PinbarTab');
    }

    /**
     * Get entity factory
     *
     * @return ItemFactory
     */
    protected function getFactory()
    {
        return $this->container->get('oro_navigation.item.factory');
    }
}
