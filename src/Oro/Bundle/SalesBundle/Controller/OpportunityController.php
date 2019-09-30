<?php

namespace Oro\Bundle\SalesBundle\Controller;

use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\EntityBundle\Tools\EntityRoutingHelper;
use Oro\Bundle\FormBundle\Model\UpdateHandler;
use Oro\Bundle\SalesBundle\Entity\Manager\AccountCustomerManager;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\SalesBundle\Form\Handler\OpportunityHandler;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Handles opportunity entity CRUD and getting info actions
 * @Route("/opportunity")
 */
class OpportunityController extends AbstractController
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices()
    {
        return array_merge(parent::getSubscribedServices(), [
            OpportunityHandler::class,
            TranslatorInterface::class,
            UpdateHandler::class,
            'oro_sales.opportunity.form' => Form::class,
            AccountCustomerManager::class,
            EntityRoutingHelper::class
        ]);
    }

    /**
     * @Route("/view/{id}", name="oro_sales_opportunity_view", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *      id="oro_sales_opportunity_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="OroSalesBundle:Opportunity"
     * )
     * @param Opportunity $entity
     * @return array
     */
    public function viewAction(Opportunity $entity)
    {
        return [
            'entity' => $entity,
        ];
    }

    /**
     * @Route("/info/{id}", name="oro_sales_opportunity_info", requirements={"id"="\d+"})
     * @Template
     * @AclAncestor("oro_sales_opportunity_view")
     * @param Opportunity $entity
     * @return array
     */
    public function infoAction(Opportunity $entity)
    {
        return [
            'entity'  => $entity
        ];
    }

    /**
     * @Route("/create", name="oro_sales_opportunity_create")
     * @Template("OroSalesBundle:Opportunity:update.html.twig")
     * @Acl(
     *      id="oro_sales_opportunity_create",
     *      type="entity",
     *      permission="CREATE",
     *      class="OroSalesBundle:Opportunity"
     * )
     */
    public function createAction()
    {
        return $this->update(new Opportunity());
    }

    /**
     * @Route("/update/{id}", name="oro_sales_opportunity_update", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Template
     * @Acl(
     *      id="oro_sales_opportunity_update",
     *      type="entity",
     *      permission="EDIT",
     *      class="OroSalesBundle:Opportunity"
     * )
     * @param Opportunity $entity
     * @return array
     */
    public function updateAction(Opportunity $entity)
    {
        return $this->update($entity);
    }

    /**
     * @Route(
     *      "/{_format}",
     *      name="oro_sales_opportunity_index",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @Template
     * @AclAncestor("oro_sales_opportunity_view")
     */
    public function indexAction()
    {
        return [
            'entity_class' => Opportunity::class
        ];
    }

    /**
     * Create opportunity form with data channel
     *
     * @Route("/create/{channelIds}", name="oro_sales_opportunity_data_channel_aware_create")
     * @Template("OroSalesBundle:Opportunity:update.html.twig")
     * @AclAncestor("oro_sales_opportunity_create")
     *
     * @ParamConverter(
     *      "channel",
     *      class="OroChannelBundle:Channel",
     *      options={"id" = "channelIds"}
     * )
     * @param Channel $channel
     * @return array
     */
    public function opportunityWithDataChannelCreateAction(Channel $channel)
    {
        $opportunity = new Opportunity();
        $opportunity->setDataChannel($channel);

        return $this->update($opportunity);
    }

    /**
     * Create opportunity form with customer association set
     *
     * @Route("/create/{targetClass}/{targetId}", name="oro_sales_opportunity_customer_aware_create")
     * @Template("OroSalesBundle:Opportunity:update.html.twig")
     * @AclAncestor("oro_sales_opportunity_create")
     * @param $targetClass
     * @param $targetId
     * @return array
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function opportunityWithCustomerCreateAction($targetClass, $targetId)
    {
        $target = $this->get(EntityRoutingHelper::class)->getEntity($targetClass, $targetId);
        $customer = $this->get(AccountCustomerManager::class)->getAccountCustomerByTarget($target);

        $opportunity = new Opportunity();
        $opportunity->setCustomerAssociation($customer);

        return $this->update($opportunity);
    }

    /**
     * @param  Opportunity $entity
     * @return array
     */
    protected function update(Opportunity $entity)
    {
        return $this->get(UpdateHandler::class)->update(
            $entity,
            $this->get('oro_sales.opportunity.form'),
            $this->get(TranslatorInterface::class)->trans('oro.sales.controller.opportunity.saved.message'),
            $this->get(OpportunityHandler::class)
        );
    }
}
