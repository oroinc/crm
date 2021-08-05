<?php

namespace Oro\Bundle\ContactBundle\Controller\Api\Rest;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\FormBundle\Form\Handler\ApiFormHandler;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * REST API CRUD controller for ContactPhone entity.
 */
class ContactPhoneController extends RestController
{
    /**
     * REST GET list
     *
     * @ApiDoc(
     *      description="Get all phones items",
     *      resource=true
     * )
     * @AclAncestor("oro_contact_view")
     * @param int $contactId
     * @return Response
     */
    public function cgetAction($contactId)
    {
        /** @var Contact $contact */
        $contact = $this->getContactManager()->find($contactId);
        $result = [];
        if (!empty($contact)) {
            $items = $contact->getPhones();

            foreach ($items as $item) {
                $result[] = $this->getPreparedItem($item);
            }
        }

        return new JsonResponse(
            $result,
            empty($contact) ? Response::HTTP_NOT_FOUND : Response::HTTP_OK
        );
    }

    /**
     * REST GET primary phone
     *
     * @param int $contactId
     *
     * @ApiDoc(
     *      description="Get contact primary phone",
     *      resource=true
     * )
     * @AclAncestor("oro_contact_view")
     * @return Response
     */
    public function getPrimaryAction(int $contactId)
    {
        /** @var Contact $contact */
        $contact = $this->getContactManager()->find($contactId);

        if ($contact) {
            $phone = $contact->getPrimaryPhone();
        } else {
            $phone = null;
        }

        $responseData = $phone ? json_encode($this->getPreparedItem($phone)) : '';

        return new Response($responseData, $phone ? Response::HTTP_OK : Response::HTTP_NOT_FOUND);
    }

    /**
     * Create entity ContactPhone
     * oro_api_post_contact_phone
     *
     * @return Response
     *
     * @ApiDoc(
     *      description="Create entity",
     *      resource=true
     * )
     */
    public function postAction()
    {
        $response = $this->handleCreateRequest();

        return $response;
    }

    /**
     * Delete entity ContactPhone
     * oro_api_delete_contact_phone
     *
     * @param int $id
     *
     * @ApiDoc(
     *      description="Delete ContactPhone"
     * )
     *
     * @return Response
     */
    public function deleteAction(int $id)
    {
        try {
            $this->getDeleteHandler()->handleDelete($id, $this->getManager());

            return new JsonResponse(["id" => ""]);
        } catch (\Exception $e) {
            $errors["errors"] = [$e->getMessage()];
            return new JsonResponse(
                [
                    "code" => $e->getCode(),
                    "message"=>$e->getMessage(),
                    "errors" => $errors
                ],
                $e->getCode()
            );
        }
    }

    protected function getContactManager()
    {
        return $this->get('oro_contact.contact.manager.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getManager()
    {
        return $this->get('oro_contact.contact_phone.manager.api');
    }

    /**
     * {@inheritDoc}
     */
    protected function getPreparedItem($entity, $resultFields = [])
    {
        $result['id']      = $entity->getId();
        $result['owner']   = (string) $entity->getOwner();
        $result['phone']   = $entity->getPhone();
        $result['primary'] = $entity->isPrimary();

        return $result;
    }

    /**
     * @return ApiFormHandler
     */
    public function getFormHandler()
    {
        return $this->get('oro_contact.form.type.contact_phone.handler');
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        return $this->get('oro_contact.form.type.contact_phone.type');
    }
}
