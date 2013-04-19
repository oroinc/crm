<?php

namespace Oro\Bundle\WindowsBundle\Controller\Api;

use Symfony\Component\HttpKernel\Exception\HttpException;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\View\RouteRedirectView;
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
        return $this->handleView(
            $this->view(
                $this->getDoctrine()->getRepository('OroNavigationBundle:PageState')->findBy(array('user' => $this->getUser())),
                Codes::HTTP_OK
            )
        );
    }

    /**
     * Get page state
     *
     * @param int $id Page state id
     *
     * @ApiDoc(
     *  description="Get page state",
     *  resource=true,
     *  requirements={
     *      {"name"="id", "dataType"="integer"},
     *  }
     * )
     */
    public function getAction($id)
    {
        $entity = $this->getManager()->findUserBy(array('id' => (int) $id));

        return $this->handleView(
            $this->view(
                $entity,
                $entity ? Codes::HTTP_OK : Codes::HTTP_NOT_FOUND
            )
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
     *  resource=true,
     *  requirements={
     *      {"name"="id", "dataType"="integer"},
     *  }
     * )
     */
    public function putAction($id)
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
     *  resource=true,
     *  requirements={
     *      {"name"="id", "dataType"="integer"},
     *  }
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
        return $this->getDoctrine()->getManagerForClass('OroNavigationBundle:PageState');
    }
}
