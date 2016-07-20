<?php

namespace OroCRM\Bundle\SalesBundle\Controller\Api\Rest;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Util\Codes;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;

use OroCRM\Bundle\SalesBundle\Entity\Lead;
use OroCRM\Bundle\SalesBundle\Entity\LeadAddress;

/**
 * @RouteResource("address")
 * @NamePrefix("oro_api_")
 */
class LeadAddressController extends RestController implements ClassResourceInterface
{
    /**
     * REST GET list
     *
     * @ApiDoc(
     *      description="Get all addresses items",
     *      resource=true
     * )
     * @AclAncestor("orocrm_sales_lead_view")
     * @param int $leadId
     *
     * @return JsonResponse
     */
    public function cgetAction($leadId)
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
            empty($lead) ? Codes::HTTP_NOT_FOUND : Codes::HTTP_OK
        );
    }

    /**
     * REST GET primary address
     *
     * @param string $leadId
     *
     * @ApiDoc(
     *      description="Get lead primary address",
     *      resource=true
     * )
     * @AclAncestor("orocrm_sales_lead_view")
     * @return Response
     */
    public function getPrimaryAction($leadId)
    {
         /** @var Lead $lead */
        $lead = $this->getLeadManager()->find($leadId);

        if ($lead) {
            $address = $lead->getPrimaryAddress();
        } else {
            $address = null;
        }

        $responseData = $address ? json_encode($this->getPreparedItem($address)) : '';

        return new Response($responseData, $address ? Codes::HTTP_OK : Codes::HTTP_NOT_FOUND);
    }

    /**
     * REST DELETE address
     *
     * @ApiDoc(
     *      description="Delete address items",
     *      resource=true
     * )
     * @AclAncestor("orocrm_sales_lead_delete")
     * @param     $leadId
     * @param int $addressId
     *
     * @return Response
     */
    public function deleteAction($leadId, $addressId)
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
            return $this->handleView($this->view(null, Codes::HTTP_NOT_FOUND));
        }
    }

    /**
     * Get entity Manager
     *
     * @return ApiEntityManager
     */
    public function getLeadManager()
    {
        return $this->get('orocrm_sales.lead.manager.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getManager()
    {
        return $this->get('orocrm_sales.lead_address.manager.api');
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
