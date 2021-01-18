<?php

namespace Oro\Bundle\SalesBundle\Controller;

use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Entity\LeadAddress;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/lead")
 */
class LeadAddressController extends AbstractController
{
    /**
     * @Route("/address-book/{id}", name="oro_sales_lead_address_book", requirements={"id"="\d+"})
     * @Template
     * @AclAncestor("oro_sales_lead_view")
     */
    public function addressBookAction(Lead $lead)
    {
        return array(
            'entity' => $lead,
            'address_edit_acl_resource' => 'oro_sales_lead_update'
        );
    }

    /**
     * @Route(
     *      "/{leadId}/address-create",
     *      name="oro_sales_lead_address_create",
     *      requirements={"leadId"="\d+"}
     * )
     * @Template("OroSalesBundle:LeadAddress:update.html.twig")
     * @AclAncestor("oro_sales_lead_update")
     * @ParamConverter("lead", options={"id" = "leadId"})
     * @param Request $request
     * @param Lead $lead
     * @return array|RedirectResponse
     */
    public function createAction(Request $request, Lead $lead)
    {
        return $this->update($request, $lead, new LeadAddress());
    }

    /**
     * @Route(
     *      "/{leadId}/address-update/{id}",
     *      name="oro_sales_lead_address_update",
     *      requirements={"leadId"="\d+","id"="\d+"},defaults={"id"=0}
     * )
     * @Template
     * @AclAncestor("oro_sales_lead_update")
     * @ParamConverter("lead", options={"id" = "leadId"})
     * @param Request $request
     * @param Lead $lead
     * @param LeadAddress $address
     * @return array|RedirectResponse
     */
    public function updateAction(Request $request, Lead $lead, LeadAddress $address)
    {
        return $this->update($request, $lead, $address);
    }

    /**
     * @param Request $request
     * @param Lead $lead
     * @param LeadAddress $address
     * @return array
     * @throws BadRequestHttpException
     */
    protected function update(Request $request, Lead $lead, LeadAddress $address)
    {
        $responseData = array(
            'saved' => false,
            'lead' => $lead
        );

        if ($request->isMethod('GET') && !$address->getId()) {
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

        if ($this->get('oro_sales.lead_address.form.handler')->process($address)) {
            $this->getDoctrine()->getManager()->flush();
            $responseData['entity'] = $address;
            $responseData['saved'] = true;
        }

        $responseData['form'] = $this->get('oro_sales.lead_address.form')->createView();

        return $responseData;
    }
}
