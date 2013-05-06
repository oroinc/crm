<?php

namespace Oro\Bundle\ContactBundle\Controller\Api\Rest;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\Rest\Util\Codes;

use FOS\RestBundle\Controller\Annotations\NamePrefix;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Oro\Bundle\UserBundle\Annotation\Acl;

use Oro\Bundle\ContactBundle\Entity\Manager\ContactManager;
use Oro\Bundle\ContactBundle\Entity\Contact;

/**
 * @NamePrefix("oro_api_")
 */
class ContactController extends FOSRestController implements ClassResourceInterface
{

    /**
     * Delete contact
     *
     * @param int $id contact id
     * @return \Symfony\Component\HttpFoundation\Response
     * @ApiDoc(
     *      description="Delete contact",
     *      resource=true,
     *      requirements={
     *          {"name"="id", "dataType"="integer"},
     *      }
     * )
     * @Acl(
     *      id="oro_contact_contact_remove",
     *      name="Remove contact",
     *      description="Remove contact",
     *      parent="oro_contact_contact"
     * )
     */
    public function deleteAction($id)
    {
        /** @var Contact $entity */
        $entity = $this->getManager()->findContactBy(array('id' => (int) $id));

        if (!$entity) {
            return $this->handleView($this->view('', Codes::HTTP_NOT_FOUND));
        }

        $this->getManager()->deleteContact($entity);

        return $this->handleView($this->view('', Codes::HTTP_NO_CONTENT));
    }

    /**
     * @return ContactManager
     */
    protected function getManager()
    {
        return $this->get('oro_contact.manager');
    }
}
