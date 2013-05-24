<?php

namespace Oro\Bundle\ContactBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use YsTools\BackUrlBundle\Annotation\BackUrl;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oro\Bundle\UserBundle\Annotation\Acl;

use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Datagrid\ContactDatagridManager;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiFlexibleEntityManager;

/**
 * @Acl(
 *      id="oro_contact",
 *      name="Contact manipulation",
 *      description="Contact manipulation",
 *      parent="root"
 * )
 * @BackUrl("back", useSession=true)
 */
class ContactController extends Controller
{
    /**
     * @Route("/view/{id}", name="oro_contact_view", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *      id="oro_contact_view",
     *      name="View Contact",
     *      description="View contact",
     *      parent="oro_contact"
     * )
     */
    public function viewAction(Contact $contact)
    {
        return array(
            'contact' => $contact,
        );
    }

    /**
     * Create contact form
     *
     * @Route("/create", name="oro_contact_create")
     * @Template("OroContactBundle:Contact:update.html.twig")
     * @Acl(
     *      id="oro_contact_create",
     *      name="Create Contact",
     *      description="Create contact",
     *      parent="oro_contact"
     * )
     */
    public function createAction()
    {
        /** @var Contact $contact */
        $contact = $this->getManager()->createEntity();
        return $this->updateAction($contact);
    }

    /**
     * Update user form
     *
     * @Route("/update/{id}", name="oro_contact_update", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Template
     * @Acl(
     *      id="oro_contact_update",
     *      name="Update Contact",
     *      description="Update contact",
     *      parent="oro_contact"
     * )
     */
    public function updateAction(Contact $entity)
    {
        $backUrl = $this->generateUrl('oro_contact_index');

        if ($this->get('oro_contact.form.handler.contact')->process($entity)) {
            $this->getFlashBag()->add('success', 'Contact successfully saved');

            BackUrl::triggerRedirect();

            return $this->redirect($backUrl);
        }

        return array(
            'entity' => $entity,
            'form'   => $this->get('oro_contact.form.contact')->createView(),
        );
    }

    /**
     * @Route(
     *      "/{_format}",
     *      name="oro_contact_index",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @Acl(
     *      id="oro_contact_list",
     *      name="View List of Contacts",
     *      description="View list of contacts",
     *      parent="oro_contact"
     * )
     */
    public function indexAction(Request $request)
    {
        /** @var $gridManager ContactDatagridManager */
        $gridManager = $this->get('oro_contact.contact.datagrid_manager');
        $datagrid = $gridManager->getDatagrid();

        if ('json' == $request->getRequestFormat()) {
            $view = 'OroGridBundle:Datagrid:list.json.php';
        } else {
            $view = 'OroContactBundle:Contact:index.html.twig';
        }

        return $this->render(
            $view,
            array('datagrid' => $datagrid->createView())
        );
    }

    /**
     * @return FlashBag
     */
    protected function getFlashBag()
    {
        return $this->get('session')->getFlashBag();
    }

    /**
     * @return ApiFlexibleEntityManager
     */
    protected function getManager()
    {
        return $this->get('oro_contact.contact.manager');
    }
}
