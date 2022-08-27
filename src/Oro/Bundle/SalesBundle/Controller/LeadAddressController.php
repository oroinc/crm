<?php

namespace Oro\Bundle\SalesBundle\Controller;

use Oro\Bundle\AddressBundle\Form\Handler\AddressHandler;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Entity\LeadAddress;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * The controller for LeadAddress entity.
 * @Route("/lead")
 */
class LeadAddressController extends AbstractController
{
    /**
     * @Route("/address-book/{id}", name="oro_sales_lead_address_book", requirements={"id"="\d+"})
     * @Template
     * @AclAncestor("oro_sales_lead_view")
     */
    public function addressBookAction(Lead $lead): array
    {
        return [
            'entity' => $lead,
            'address_edit_acl_resource' => 'oro_sales_lead_update'
        ];
    }

    /**
     * @Route(
     *      "/{leadId}/address-create",
     *      name="oro_sales_lead_address_create",
     *      requirements={"leadId"="\d+"}
     * )
     * @Template("@OroSales/LeadAddress/update.html.twig")
     * @AclAncestor("oro_sales_lead_update")
     * @ParamConverter("lead", options={"id" = "leadId"})
     */
    public function createAction(Request $request, Lead $lead): array|RedirectResponse
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
     */
    public function updateAction(Request $request, Lead $lead, LeadAddress $address): array|RedirectResponse
    {
        return $this->update($request, $lead, $address);
    }

    /**
     * @throws BadRequestHttpException
     */
    protected function update(Request $request, Lead $lead, LeadAddress $address): array|RedirectResponse
    {
        $responseData = [
            'saved' => false,
            'lead' => $lead
        ];

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
        $form = $this->get('oro_sales.lead_address.form');
        if ($this->get(AddressHandler::class)->process(
            $address,
            $form,
            $request
        )) {
            $this->getDoctrine()->getManager()->flush();
            $responseData['entity'] = $address;
            $responseData['saved'] = true;
        }

        $responseData['form'] = $form->createView();

        return $responseData;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedServices()
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                'oro_sales.lead_address.form' => Form::class,
                AddressHandler::class,
            ]
        );
    }
}
