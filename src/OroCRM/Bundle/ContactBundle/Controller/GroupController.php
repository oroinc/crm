<?php

namespace OroCRM\Bundle\ContactBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use OroCRM\Bundle\ContactBundle\Entity\Group;

/**
 * @Route("/group")
 */
class GroupController extends Controller
{
    /**
     * Create group form
     *
     * @Route("/create", name="orocrm_contact_group_create")
     * @Template("OroCRMContactBundle:Group:update.html.twig")
     * @Acl(
     *      id="orocrm_contact_group_create",
     *      type="entity",
     *      permission="CREATE",
     *      class="OroCRMContactBundle:Group"
     * )
     */
    public function createAction()
    {
        return $this->update(new Group());
    }

    /**
     * Update group form
     *
     * @Route("/update/{id}", name="orocrm_contact_group_update", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Template
     * @Acl(
     *      id="orocrm_contact_group_update",
     *      type="entity",
     *      permission="EDIT",
     *      class="OroCRMContactBundle:Group"
     * )
     */
    public function updateAction(Group $entity)
    {
        return $this->update($entity);
    }

    /**
     * @Route(
     *      "/{_format}",
     *      name="orocrm_contact_group_index",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @Acl(
     *      id="orocrm_contact_group_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="OroCRMContactBundle:Group"
     * )
     * @Template()
     */
    public function indexAction()
    {
        return [
            'entity_class' => $this->container->getParameter('orocrm_contact.group.entity.class')
        ];
    }

    /**
     * @param Group $entity
     *
     * @return array
     */
    protected function update(Group $entity)
    {
        if ($this->get('orocrm_contact.form.handler.group')->process($entity)) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('orocrm.contact.controller.contact_group.saved.message')
            );

            if (!$this->getRequest()->get('_widgetContainer')) {
                return $this->get('oro_ui.router')->redirect($entity);
            }
        }

        return array(
            'entity'           => $entity,
            'form'             => $this->get('orocrm_contact.form.group')->createView(),
            'showContactsGrid' => count($this->get('orocrm_contact.contact.manager')->getList()) ? true : false
        );
    }
}
