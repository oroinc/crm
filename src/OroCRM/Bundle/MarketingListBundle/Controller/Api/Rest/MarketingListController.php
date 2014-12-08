<?php

namespace OroCRM\Bundle\MarketingListBundle\Controller\Api\Rest;

use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Util\Codes;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Routing\ClassResourceInterface;

use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use OroCRM\Bundle\MarketingListBundle\Model\ContactInformationFieldHelper;

/**
 * @Rest\RouteResource("marketinglist")
 * @Rest\NamePrefix("orocrm_api_")
 */
class MarketingListController extends RestController implements ClassResourceInterface
{
    /**
     * REST DELETE
     *
     * @param int $id
     *
     * @ApiDoc(
     *      description="Delete Marketing List",
     *      resource=true
     * )
     * @AclAncestor("orocrm_marketing_list_delete")
     *
     * @return Response
     */
    public function deleteAction($id)
    {
        return $this->handleDeleteRequest($id);
    }

    /**
     * @Rest\Get(
     *      "/marketinglist/contact-information/field/type"
     * )
     * @ApiDoc(
     *     description="Get contact information field type by field name",
     *     resource=true
     * )
     * @return Response
     */
    public function contactInformationFieldTypeAction()
    {
        $entity = $this->getRequest()->get('entity');
        $field = $this->getRequest()->get('field');
        /** @var ContactInformationFieldHelper $helper */
        $helper = $this->get('orocrm_marketing_list.contact_information_field_helper');
        return $this->handleView(
            $this->view(
                $helper->getContactInformationFieldType($entity, $field),
                Codes::HTTP_OK
            )
        );
    }

    /**
     * @Rest\Get(
     *      "/marketinglist/contact-information/entity/fields"
     * )
     * @ApiDoc(
     *     description="Get entity contact information fields",
     *     resource=true
     * )
     * @return Response
     */
    public function entityContactInformationFieldsAction()
    {
        $entity = $this->getRequest()->get('entity');
        /** @var ContactInformationFieldHelper $helper */
        $helper = $this->get('orocrm_marketing_list.contact_information_field_helper');

        return $this->handleView($this->view($helper->getEntityContactInformationColumnsInfo($entity), Codes::HTTP_OK));
    }

    /**
     * {@inheritdoc}
     */
    public function getManager()
    {
        return $this->get('orocrm_marketing_list.marketing_list.manager.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        throw new \BadMethodCallException('Form is not available.');
    }

    /**
     * {@inheritdoc}
     */
    public function getFormHandler()
    {
        throw new \BadMethodCallException('FormHandler is not available.');
    }
}
