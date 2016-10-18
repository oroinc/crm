<?php

namespace Oro\Bundle\ContactBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\ContactBundle\Entity\Group;

/**
 * @Route("/group")
 */
class GroupController extends Controller
{
    /**
     * Create group form
     *
     * @Route("/create", name="oro_contact_group_create")
     * @Template("OroContactBundle:Group:update.html.twig")
     * @Acl(
     *      id="oro_contact_group_create",
     *      type="entity",
     *      permission="CREATE",
     *      class="OroContactBundle:Group"
     * )
     */
    public function createAction()
    {
        return $this->update(new Group());
    }

    /**
     * Update group form
     *
     * @Route("/update/{id}", name="oro_contact_group_update", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Template
     * @Acl(
     *      id="oro_contact_group_update",
     *      type="entity",
     *      permission="EDIT",
     *      class="OroContactBundle:Group"
     * )
     */
    public function updateAction(Group $entity)
    {
        return $this->update($entity);
    }

    /**
     * @Route(
     *      "/{_format}",
     *      name="oro_contact_group_index",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @Acl(
     *      id="oro_contact_group_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="OroContactBundle:Group"
     * )
     * @Template()
     */
    public function indexAction()
    {
        return [
            'entity_class' => $this->container->getParameter('oro_contact.group.entity.class')
        ];
    }

    /**
     * @param Group $entity
     *
     * @return array
     */
    protected function update(Group $entity)
    {
        if ($this->get('oro_contact.form.handler.group')->process($entity)) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('oro.contact.controller.contact_group.saved.message')
            );

            if (!$this->getRequest()->get('_widgetContainer')) {
                return $this->get('oro_ui.router')->redirect($entity);
            }
        }

        return array(
            'entity'           => $entity,
            'form'             => $this->get('oro_contact.form.group')->createView(),
            'showContactsGrid' => count($this->get('oro_contact.contact.manager')->getList()) ? true : false
        );
    }
}
