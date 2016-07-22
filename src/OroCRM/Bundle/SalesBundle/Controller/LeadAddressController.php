<?php

namespace OroCRM\Bundle\SalesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use OroCRM\Bundle\SalesBundle\Entity\LeadAddress;
use OroCRM\Bundle\SalesBundle\Entity\Lead;

/**
 * @Route("/lead")
 */
class LeadAddressController extends Controller
{
    /**
     * @Route("/address-book/{id}", name="orocrm_sales_lead_address_book", requirements={"id"="\d+"})
     * @Template
     * @AclAncestor("orocrm_sales_lead_view")
     */
    public function addressBookAction(Lead $lead)
    {
        return array(
            'entity' => $lead,
            'address_edit_acl_resource' => 'orocrm_sales_lead_update'
        );
    }

    /**
     * @Route(
     *      "/{leadId}/address-create",
     *      name="orocrm_sales_lead_address_create",
     *      requirements={"leadId"="\d+"}
     * )
     * @Template("OroCRMSalesBundle:LeadAddress:update.html.twig")
     * @AclAncestor("orocrm_sales_lead_update")
     * @ParamConverter("lead", options={"id" = "leadId"})
     */
    public function createAction(Lead $lead)
    {
        return $this->update($lead, new LeadAddress());
    }

    /**
     * @Route(
     *      "/{leadId}/address-update/{id}",
     *      name="orocrm_sales_lead_address_update",
     *      requirements={"leadId"="\d+","id"="\d+"},defaults={"id"=0}
     * )
     * @Template
     * @AclAncestor("orocrm_sales_lead_update")
     * @ParamConverter("lead", options={"id" = "leadId"})
     */
    public function updateAction(Lead $lead, LeadAddress $address)
    {
        return $this->update($lead, $address);
    }

    /**
     * @param Lead $lead
     * @param LeadAddress $address
     * @return array
     * @throws BadRequestHttpException
     */
    protected function update(Lead $lead, LeadAddress $address)
    {
        $responseData = array(
            'saved' => false,
            'lead' => $lead
        );

        if ($this->getRequest()->getMethod() == 'GET' && !$address->getId()) {
            $address->setFirstName($lead->getFirstName());
            $address->setLastName($lead->getLastName());
            if (!$lead->getAddresses()->count()) {
                $address->setPrimary(true);
            }
        }

        if ($address->getOwner() && $address->getOwner()->getId() != $lead->getId()) {
            throw new BadRequestHttpException('Address must belong to lead');
        } elseif (!$address->getOwner()) {
            $lead->addAddress($address);
        }

        // Update lead's modification date when an address is changed
        $lead->setUpdatedAt(new \DateTime('now', new \DateTimeZone('UTC')));

        if ($this->get('orocrm_sales.lead_address.form.handler')->process($address)) {
            $this->getDoctrine()->getManager()->flush();
            $responseData['entity'] = $address;
            $responseData['saved'] = true;
        }

        $responseData['form'] = $this->get('orocrm_sales.lead_address.form')->createView();

        return $responseData;
    }
}
