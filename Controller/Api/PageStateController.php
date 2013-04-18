<?php

namespace Oro\Bundle\WindowsBundle\Controller\Api;

use Symfony\Component\HttpKernel\Exception\HttpException;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\Rest\Util\Codes;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 * @NamePrefix("oro_api_")
 */
class PageStateController extends FOSRestController
{
    /**
     * Get list of user's page states
     *
     * @ApiDoc(
     *  description="Get list of user's page states",
     *  resource=true
     * )
     */
    public function cgetAction()
    {
        $items = $this->getDoctrine()->getRepository('OroNavigationBundle:PageState')->findBy(array('user' => $this->getUser()));

        return $this->handleView(
            $this->view($items, is_array($items) ? Codes::HTTP_OK : Codes::HTTP_NOT_FOUND)
        );
    }

    /**
     * Create new page state
     *
     * @ApiDoc(
     *  description="Create new page state",
     *  resource=true
     * )
     */
    public function postAction()
    {
        return $this->handleView($this->view(array(), Codes::HTTP_CREATED));
    }

    /**
     * Update existing page state
     *
     * @param int $id Page state id
     *
     * @ApiDoc(
     *  description="Update existing page state",
     *  resource=true
     * )
     */
    public function putAction($windowId)
    {
        return $this->handleView($this->view(array(), Codes::HTTP_OK));
    }

    /**
     * Remove page state
     *
     * @param int $d
     *
     * @ApiDoc(
     *  description="Remove page state",
     *  resource=true
     * )
     */
    public function deleteAction($id)
    {
        return $this->handleView($this->view(array(), Codes::HTTP_NO_CONTENT));
    }

    /**
     * Get entity Manager
     *
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    protected function getManager()
    {
        return $this->getDoctrine()->getEntityManagerForClass('OroNavigationBundle:PageState');
    }
}
