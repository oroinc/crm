<?php

namespace Oro\Bundle\WindowsBundle\Controller\Api;

use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\Rest\Util\Codes;
use Oro\Bundle\UserBundle\Entity\User;

use Oro\Bundle\WindowsBundle\Entity\WindowsState;
use Oro\Bundle\WindowsBundle\Entity\Repository\WindowsStateRepository;

/**
 * @RouteResource("windows")
 * @NamePrefix("oro_api_")
 */
class WindowsStateController extends FOSRestController
{
    /**
     * REST GET list
     *
     * @ApiDoc(
     *  description="Get all Windows States for user",
     *  resource=true
     * )
     * @return Response
     */
    public function cgetAction()
    {
        /** @var $repo WindowsStateRepository */
        $repo = $this->getDoctrine()->getRepository('\Oro\Bundle\WindowsBundle\Entity\WindowsState');
        $items = $repo->getWindowsStates($this->getUserId());

        return $this->handleView(
            $this->view($items, is_array($items) ? Codes::HTTP_OK : Codes::HTTP_NOT_FOUND)
        );
    }

    /**
     * REST POST
     *
     * @ApiDoc(
     *  description="Add Windows State",
     *  resource=true
     * )
     * @return Response
     */
    public function postAction()
    {
        $postArray = $this->getRequest()->request->all();
        if (empty($postArray) || empty($postArray['data']) || !($jsonDataString = json_encode($postArray['data']))) {
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

        /** @var $entity \Oro\Bundle\WindowsBundle\Entity\WindowsState */
        $entity = new WindowsState();
        $entity->setData($jsonDataString);
        $entity->setUser($user);

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
     * @param int $windowId Window state id
     *
     * @ApiDoc(
     *  description="Update Navigation item",
     *  resource=true
     * )
     * @return Response
     */
    public function putAction($windowId)
    {
        $postArray = $this->getRequest()->request->all();
        if (empty($postArray) || empty($postArray['data']) || !($jsonDataString = json_encode($postArray['data']))) {
            return $this->handleView(
                $this->view(
                    array('message' => 'Wrong JSON inside POST body'),
                    Codes::HTTP_BAD_REQUEST
                )
            );
        }

        /** @var $entity \Oro\Bundle\WindowsBundle\Entity\WindowsState */
        $entity = $this->getManager()->find('OroWindowsBundle:WindowsState', (int)$windowId);
        if (!$entity) {
            return $this->handleView($this->view(array(), Codes::HTTP_NOT_FOUND));
        }
        if (!$this->validatePermissions($entity->getUser())) {
            return $this->handleView($this->view(array(), Codes::HTTP_FORBIDDEN));
        }

        $entity->setData($jsonDataString);

        $em = $this->getManager();
        $em->persist($entity);
        $em->flush();

        return $this->handleView($this->view(array(), Codes::HTTP_OK));
    }

    /**
     * REST DELETE
     *
     * @param int $windowId
     *
     * @ApiDoc(
     *  description="Remove Navigation item",
     *  resource=true
     * )
     * @return Response
     */
    public function deleteAction($windowId)
    {
        /** @var $entity \Oro\Bundle\WindowsBundle\Entity\WindowsState */
        $entity = $this->getManager()->find('OroWindowsBundle:WindowsState', (int)$windowId);
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
}
