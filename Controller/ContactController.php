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
use Oro\Bundle\ContactBundle\Entity\Manager\ContactManager;
use Oro\Bundle\ContactBundle\Datagrid\ContactDatagridManager;

/**
 * @Acl(
 *      id="oro_contact_contact",
 *      name="contact controller",
 *      description="contact manipulation",
 *      parent="oro_contact"
 * )
 * @BackUrl("back", useSession=true)
 */
class ContactController extends Controller
{
    /**
     * @Route("/show/{id}", name="oro_contact_show", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *      id="oro_contact_contact_show",
     *      name="View contact",
     *      description="View contact",
     *      parent="oro_contact_contact"
     * )
     */
    public function showAction(Contact $contact)
    {
        return array(
            'contact' => $contact,
        );
    }

    /**
     * Create contact form
     *
     * @Route("/create", name="oro_contact_create")
     * @Template("OroContactBundle:Contact:edit.html.twig")
     * @Acl(
     *      id="oro_contact_contact_create",
     *      name="Create contact",
     *      description="Create contact",
     *      parent="oro_contact_contact"
     * )
     */
    public function createAction()
    {
        $contact = $this->getManager()->createFlexible();
        return $this->editAction($contact);
    }

    /**
     * Edit user form
     *
     * @Route("/edit/{id}", name="oro_contact_edit", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Template
     * @Acl(
     *      id="oro_contact_contact_edit",
     *      name="Edit contact",
     *      description="Edit contact",
     *      parent="oro_contact_contact"
     * )
     */
    public function editAction(Contact $entity)
    {
        $backUrl = $this->generateUrl('oro_contact_index');

        if ($this->get('oro_contact.form.handler.contact')->process($entity)) {
            $this->getFlashBag()->add('success', 'Contact successfully saved');
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
     *      id="oro_contact_contact_list",
     *      name="View list of contacts",
     *      description="View list of contacts",
     *      parent="oro_contact_contact"
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
     * @return ContactManager
     */
    protected function getManager()
    {
        return $this->get('oro_contact.manager');
    }
}
