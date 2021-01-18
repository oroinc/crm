<?php

namespace Oro\Bundle\ContactBundle\Controller;

use Oro\Bundle\ContactBundle\Entity\Group;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * The controller for Group entity.
 * @Route("/group")
 */
class GroupController extends AbstractController
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
     * @param Request $request
     * @return array|RedirectResponse
     */
    public function createAction(Request $request)
    {
        return $this->update($request, new Group());
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
     * @param Request $request
     * @param Group $entity
     * @return array|RedirectResponse
     */
    public function updateAction(Request $request, Group $entity)
    {
        return $this->update($request, $entity);
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
            'entity_class' => Group::class
        ];
    }

    /**
     * @param Group $entity
     * @param Request $request
     *
     * @return array|RedirectResponse
     */
    protected function update(Request $request, Group $entity)
    {
        if ($this->get('oro_contact.form.handler.group')->process($entity)) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('oro.contact.controller.contact_group.saved.message')
            );

            if (!$request->get('_widgetContainer')) {
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
