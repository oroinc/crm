<?php

namespace Oro\Bundle\SalesBundle\Controller;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\FormBundle\Model\UpdateHandlerFacade;
use Oro\Bundle\SalesBundle\Entity\B2bCustomer;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\SalesBundle\Form\Handler\B2bCustomerHandler;
use Oro\Bundle\SalesBundle\Form\Type\B2bCustomerType;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * The controller for B2bCustomer entity.
 * @Route("/b2bcustomer")
 */
class B2bCustomerController extends AbstractController
{
    /**
     * @Route(
     *      "/{_format}",
     *      name="oro_sales_b2bcustomer_index",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format"="html"}
     * )
     * @Template
     * @AclAncestor("oro_sales_b2bcustomer_view")
     */
    public function indexAction(): array
    {
        return [
            'entity_class' => B2bCustomer::class
        ];
    }

    /**
     * @Route("/view/{id}", name="oro_sales_b2bcustomer_view", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *      id="oro_sales_b2bcustomer_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="OroSalesBundle:B2bCustomer"
     * )
     */
    public function viewAction(B2bCustomer $customer): array
    {
        return [
            'entity' => $customer
        ];
    }

    /**
     * @Route("/widget/info/{id}", name="oro_sales_b2bcustomer_widget_info", requirements={"id"="\d+"})
     * @AclAncestor("oro_sales_b2bcustomer_view")
     * @Template
     */
    public function infoAction(B2bCustomer $customer): array
    {
        return [
            'entity' => $customer
        ];
    }

    /**
     * @Route("/widget/b2bcustomer-leads/{id}", name="oro_sales_b2bcustomer_widget_leads", requirements={"id"="\d+"})
     * @AclAncestor("oro_sales_lead_view")
     * @Template
     */
    public function b2bCustomerLeadsAction(B2bCustomer $customer): array
    {
        return [
            'entity' => $customer
        ];
    }

    /**
     * Create b2bcustomer form
     *
     * @Route("/create", name="oro_sales_b2bcustomer_create")
     * @Acl(
     *      id="oro_sales_b2bcustomer_create",
     *      type="entity",
     *      permission="CREATE",
     *      class="OroSalesBundle:B2bCustomer"
     * )
     * @Template("@OroSales/B2bCustomer/update.html.twig")
     */
    public function createAction(): array|RedirectResponse
    {
        return $this->update(new B2bCustomer());
    }

    protected function update(B2bCustomer $entity = null): array|RedirectResponse
    {
        return $this->get(UpdateHandlerFacade::class)->update(
            $entity,
            $this->createForm(B2bCustomerType::class, $entity),
            $this->get(TranslatorInterface::class)->trans('oro.sales.controller.b2bcustomer.saved.message'),
            null,
            $this->get(B2bCustomerHandler::class)
        );
    }

    /**
     * Update user form
     * @Route("/update/{id}", name="oro_sales_b2bcustomer_update", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Template
     * @Acl(
     *      id="oro_sales_b2bcustomer_update",
     *      type="entity",
     *      permission="EDIT",
     *      class="OroSalesBundle:B2bCustomer"
     * )
     */
    public function updateAction(B2bCustomer $entity): array|RedirectResponse
    {
        return $this->update($entity);
    }

    /**
     * @Route(
     *      "/widget/b2bcustomer-opportunities/{id}",
     *      name="oro_sales_b2bcustomer_widget_opportunities",
     *      requirements={"id"="\d+"}
     * )
     * @AclAncestor("oro_sales_opportunity_view")
     * @Template
     */
    public function b2bCustomerOpportunitiesAction(B2bCustomer $customer): array
    {
        return [
            'entity' => $customer
        ];
    }

    /**
     * @Route(
     *      "/widget/b2bcustomers-info/account/{accountId}/channel/{channelId}",
     *      name="oro_sales_widget_account_b2bcustomers_info",
     *      requirements={"accountId"="\d+", "channelId"="\d+"}
     * )
     * @ParamConverter("account", class="OroAccountBundle:Account", options={"id" = "accountId"})
     * @ParamConverter("channel", class="OroChannelBundle:Channel", options={"id" = "channelId"})
     * @AclAncestor("oro_sales_b2bcustomer_view")
     * @Template
     */
    public function accountCustomersInfoAction(Account $account, Channel $channel): array
    {
        $customers = $this->getDoctrine()
            ->getRepository('OroSalesBundle:B2bCustomer')
            ->findBy(['account' => $account, 'dataChannel' => $channel]);

        return ['account' => $account, 'customers' => $customers, 'channel' => $channel];
    }

    /**
     * @Route(
     *        "/widget/b2bcustomer-info/{id}/channel/{channelId}",
     *        name="oro_sales_widget_b2bcustomer_info",
     *        requirements={"id"="\d+", "channelId"="\d+"}
     * )
     * @ParamConverter("channel", class="OroChannelBundle:Channel", options={"id" = "channelId"})
     * @AclAncestor("oro_sales_b2bcustomer_view")
     * @Template
     */
    public function customerInfoAction(B2bCustomer $customer, Channel $channel): array
    {
        return [
            'customer'             => $customer,
            'channel'              => $channel,
            'leadClassName'        => Lead::class,
            'opportunityClassName' => Opportunity::class
        ];
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedServices()
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                TranslatorInterface::class,
                B2bCustomerHandler::class,
                UpdateHandlerFacade::class
            ]
        );
    }
}
