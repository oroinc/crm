<?php

namespace Oro\Bundle\SalesBundle\Controller\Api\Rest;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Entity\LeadAddress;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * REST API CRUD controller for LeadAddress entity.
 */
class LeadAddressController extends RestController
{
    /**
     * REST GET list
     *
     * @ApiDoc(
     *      description="Get all addresses items",
     *      resource=true
     * )
     * @AclAncestor("oro_sales_lead_view")
     * @param int $leadId
     *
     * @return JsonResponse
     */
    public function cgetAction(int $leadId)
    {
        /** @var Lead $lead */
        $lead = $this->getLeadManager()->find($leadId);
        $result  = [];

        if (!empty($lead)) {
            $items = $lead->getAddresses();

            foreach ($items as $item) {
                $result[] = $this->getPreparedItem($item);
            }
        }

        return new JsonResponse(
            $result,
            empty($lead) ? Response::HTTP_NOT_FOUND : Response::HTTP_OK
        );
    }

    /**
     * REST GET primary address
     *
     * @param int $leadId
     *
     * @ApiDoc(
     *      description="Get lead primary address",
     *      resource=true
     * )
     * @AclAncestor("oro_sales_lead_view")
     * @return Response
     */
    public function getPrimaryAction(int $leadId)
    {
        /** @var Lead $lead */
        $lead = $this->getLeadManager()->find($leadId);

        if ($lead) {
            $address = $lead->getPrimaryAddress();
        } else {
            $address = null;
        }

        $responseData = $address ? json_encode($this->getPreparedItem($address)) : '';

        return new Response($responseData, $address ? Response::HTTP_OK : Response::HTTP_NOT_FOUND);
    }

    /**
     * REST DELETE address
     *
     * @ApiDoc(
     *      description="Delete address items",
     *      resource=true
     * )
     * @AclAncestor("oro_sales_lead_delete")
     * @param int $leadId
     * @param int $addressId
     *
     * @return Response
     */
    public function deleteAction(int $leadId, int $addressId)
    {
        /** @var LeadAddress $address */
        $address = $this->getManager()->find($addressId);
        /** @var Lead $lead */
        $lead = $this->getLeadManager()->find($leadId);
        if ($lead->getAddresses()->contains($address)) {
            $lead->removeAddress($address);
            // Update lead's modification date when an address is removed
            $lead->setUpdatedAt(new \DateTime('now', new \DateTimeZone('UTC')));
            return $this->handleDeleteRequest($addressId);
        } else {
            return $this->handleView($this->view(null, Response::HTTP_NOT_FOUND));
        }
    }

    /**
     * Get entity Manager
     *
     * @return ApiEntityManager
     */
    protected function getLeadManager()
    {
        return $this->get('oro_sales.lead.manager.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getManager()
    {
        return $this->get('oro_sales.lead_address.manager.api');
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

    /**
     * {@inheritDoc}
     */
    protected function getPreparedItem($entity, $resultFields = [])
    {
        $result = parent::getPreparedItem($entity);
        $result['countryIso2'] = $entity->getCountryIso2();
        $result['countryIso3'] = $entity->getCountryIso2();
        $result['regionCode']  = $entity->getRegionCode();
        $result['country'] = $entity->getCountryName();

        unset($result['owner']);

        return $result;
    }
}
