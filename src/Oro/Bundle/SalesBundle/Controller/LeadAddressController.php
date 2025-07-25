<?php

namespace Oro\Bundle\SalesBundle\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\AddressBundle\Form\Handler\AddressHandler;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Entity\LeadAddress;
use Oro\Bundle\SecurityBundle\Attribute\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;

/**
 * The controller for LeadAddress entity.
 */
#[Route(path: '/lead')]
class LeadAddressController extends AbstractController
{
    #[Route(path: '/address-book/{id}', name: 'oro_sales_lead_address_book', requirements: ['id' => '\d+'])]
    #[Template]
    #[AclAncestor('oro_sales_lead_view')]
    public function addressBookAction(Lead $lead): array
    {
        return [
            'entity' => $lead,
            'address_edit_acl_resource' => 'oro_sales_lead_update'
        ];
    }

    #[Route(
        path: '/{leadId}/address-create',
        name: 'oro_sales_lead_address_create',
        requirements: ['leadId' => '\d+']
    )]
    #[Template('@OroSales/LeadAddress/update.html.twig')]
    #[ParamConverter('lead', options: ['id' => 'leadId'])]
    #[AclAncestor('oro_sales_lead_update')]
    public function createAction(Request $request, Lead $lead): array|RedirectResponse
    {
        return $this->update($request, $lead, new LeadAddress());
    }

    #[Route(
        path: '/{leadId}/address-update/{id}',
        name: 'oro_sales_lead_address_update',
        requirements: ['leadId' => '\d+', 'id' => '\d+'],
        defaults: ['id' => 0]
    )]
    #[Template]
    #[ParamConverter('lead', options: ['id' => 'leadId'])]
    #[AclAncestor('oro_sales_lead_update')]
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
        $form = $this->container->get('oro_sales.lead_address.form');
        if ($this->container->get(AddressHandler::class)->process(
            $address,
            $form,
            $request
        )) {
            $this->container->get('doctrine')->getManager()->flush();
            $responseData['entity'] = $address;
            $responseData['saved'] = true;
        }

        $responseData['form'] = $form->createView();

        return $responseData;
    }

    #[\Override]
    public static function getSubscribedServices(): array
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                'oro_sales.lead_address.form' => Form::class,
                AddressHandler::class,
                'doctrine' => ManagerRegistry::class,
            ]
        );
    }
}
