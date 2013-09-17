<?php

namespace OroCRM\Bundle\ContactBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\DeserializationContext;
use Symfony\Component\Serializer\SerializerInterface;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oro\Bundle\UserBundle\Annotation\Acl;
use Oro\Bundle\UserBundle\Annotation\AclAncestor;

use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\ContactBundle\Datagrid\ContactDatagridManager;
use OroCRM\Bundle\ContactBundle\Datagrid\ContactAccountDatagridManager;
use OroCRM\Bundle\ContactBundle\Datagrid\ContactAccountUpdateDatagridManager;
use OroCRM\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ImportExportBundle\Serializer\Serializer;
use OroCRM\Bundle\ContactBundle\ImportExport\Converter\ContactDataConverter;

/**
 * @Acl(
 *      id="orocrm_contact",
 *      name="Contact manipulation",
 *      description="Contact manipulation",
 *      parent="root"
 * )
 */
class ContactController extends Controller
{
    /**
     * @Route("/view/{id}", name="orocrm_contact_view", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *      id="orocrm_contact_view",
     *      name="View Contact",
     *      description="View contact",
     *      parent="orocrm_contact"
     * )
     */
    public function viewAction(Contact $contact)
    {
        /** @var $accountDatagridManager ContactAccountDatagridManager */
        $accountDatagridManager = $this->get('orocrm_contact.account.view_datagrid_manager');
        $accountDatagridManager->setContact($contact);
        $datagridView = $accountDatagridManager->getDatagrid()->createView();

        if ('json' == $this->getRequest()->getRequestFormat()) {
            return $this->get('oro_grid.renderer')->renderResultsJsonResponse($datagridView);
        }

        return array(
            'entity'   => $contact,
            'datagrid' => $datagridView,
        );
    }

    /**
     * @Route("/info/{id}", name="orocrm_contact_info", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *      id="orocrm_contact_info",
     *      name="View Contact Info",
     *      description="View contact info",
     *      parent="orocrm_contact_view"
     * )
     */
    public function infoAction(Contact $contact)
    {
        return array(
            'entity' => $contact
        );
    }

    /**
     * Create contact form
     *
     * @Route("/create", name="orocrm_contact_create")
     * @Template("OroCRMContactBundle:Contact:update.html.twig")
     * @Acl(
     *      id="orocrm_contact_create",
     *      name="Create Contact",
     *      description="Create contact",
     *      parent="orocrm_contact"
     * )
     */
    public function createAction()
    {
        // add predefined account to contact
        $contact = null;
        $accountId = $this->getRequest()->get('account');
        if ($accountId) {
            $repository = $this->getDoctrine()->getRepository('OroCRMAccountBundle:Account');
            /** @var Account $account */
            $account = $repository->find($accountId);
            if ($account) {
                /** @var Contact $contact */
                $contact = $this->getManager()->createEntity();
                $contact->addAccount($account);
            } else {
                throw new NotFoundHttpException(sprintf('Account with ID %s is not found', $accountId));
            }
        }

        return $this->updateAction($contact);
    }

    /**
     * Update user form
     *
     * @Route("/update/{id}", name="orocrm_contact_update", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Template
     * @Acl(
     *      id="orocrm_contact_update",
     *      name="Update Contact",
     *      description="Update contact",
     *      parent="orocrm_contact"
     * )
     */
    public function updateAction(Contact $entity = null)
    {
        if (!$entity) {
            $entity = $this->getManager()->createEntity();
        }

        /** @var $accountDatagridManager ContactAccountUpdateDatagridManager */
        $accountDatagridManager = $this->get('orocrm_contact.account.update_datagrid_manager');
        $accountDatagridManager->setContact($entity);
        $datagridView = $accountDatagridManager->getDatagrid()->createView();

        if ('json' == $this->getRequest()->getRequestFormat()) {
            return $this->get('oro_grid.renderer')->renderResultsJsonResponse($datagridView);
        }

        if ($this->get('orocrm_contact.form.handler.contact')->process($entity)) {
            $this->getFlashBag()->add('success', 'Contact successfully saved');

            return $this->get('oro_ui.router')->actionRedirect(
                array(
                    'route' => 'orocrm_contact_update',
                    'parameters' => array('id' => $entity->getId()),
                ),
                array(
                    'route' => 'orocrm_contact_view',
                    'parameters' => array('id' => $entity->getId())
                )
            );
        }

        return array(
            'entity'   => $entity,
            'form'     => $this->get('orocrm_contact.form.contact')->createView(),
            'datagrid' => $datagridView,
        );
    }

    /**
     * @Route(
     *      "/{_format}",
     *      name="orocrm_contact_index",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @Template
     * @Acl(
     *      id="orocrm_contact_list",
     *      name="View List of Contacts",
     *      description="View list of contacts",
     *      parent="orocrm_contact"
     * )
     */
    public function indexAction()
    {
        /** @var $gridManager ContactDatagridManager */
        $gridManager = $this->get('orocrm_contact.contact.datagrid_manager');
        $datagridView = $gridManager->getDatagrid()->createView();

        if ('json' == $this->getRequest()->getRequestFormat()) {
            return $this->get('oro_grid.renderer')->renderResultsJsonResponse($datagridView);
        }

        return array('datagrid' => $datagridView);
    }

    /**
     * @Route(
     *      "/export{_format}",
     *      name="orocrm_contact_export",
     *      requirements={"_format"="csv"},
     *      defaults={"_format" = "csv"}
     * )
     * @Acl(
     *      id="orocrm_contact_export",
     *      name="Export List of Contacts",
     *      description="Export list of contacts",
     *      parent="orocrm_contact"
     * )
     */
    public function exportAction()
    {
        $doctrine = $this->getDoctrine();
        $contacts = $doctrine->getRepository('OroCRMContactBundle:Contact')->findAll();
        $contact = current($contacts);

        /** @var Serializer $importExportSerializer */
        $importExportSerializer = $this->get('oro_importexport.serializer');
        $serializedData = $importExportSerializer->serialize($contact, null);

        /*
        $serializedData = array(
            'id' => 69,
            'namePrefix' => 'Ms.',
            'firstName' => 'April',
            'lastName' => 'Lynch',
            'nameSuffix' => null,
            'gender' => null,
            'description' => null,
            'jobTitle' => null,
            'fax' => null,
            'skype' => null,
            'twitter' => null,
            'facebook' => null,
            'googlePlus' => null,
            'linkedIn' => null,
            'birthday' => '1944-08-29T16:52:09+0200',
            'source' => 'tv',
            'method' => null,
            'owner' => array(
                'firstName' => 'William',
                'lastName' => 'Stewart',
            ),
            'assignedTo' => array(
                'firstName' => 'William',
                'lastName' => 'Stewart',
            ),
            'addresses' => array(
                array(
                    'label' => null,
                    'firstName' => null,
                    'lastName' => null,
                    'street' => null,
                    'street2' => null,
                    'city' => null,
                    'state' => null,
                    'country' => null,
                    'postalCode' => null,
                    'types' => array()
                ),
                array(
                    'label' => null,
                    'firstName' => null,
                    'lastName' => null,
                    'street' => null,
                    'street2' => null,
                    'city' => null,
                    'state' => null,
                    'country' => null,
                    'postalCode' => null,
                    'types' => array(
                        'billing',
                        'shipping'
                    )
                ),
            ),
            'emails' => array(
                'primary-email@example.com',
                'another-email@example.com',
            ),
            'phones' => array(
                '0 800 11 22 444',
                '0 800 11 22 555',
            ),
            'groups' => array(
                'first_group',
                'second_group',
            ),
            'accounts' => array(
                'First Account Name',
                'Second Account Name',
            )
        );
        */

        /** @var ContactDataConverter $contactDataConverter */
        $contactDataConverter = $this->get('orocrm_contact.importexport.data_converter.contact');
        $contactExportData = $contactDataConverter->convertToExportFormat($serializedData);

        return new Response(
            '<pre>' . var_export($serializedData, true) . "\n\n" . var_export($contactExportData, true) .  '</pre>'
        );
    }

    /**
     * @Route(
     *      "/import{_format}",
     *      name="orocrm_contact_import",
     *      requirements={"_format"="csv"},
     *      defaults={"_format" = "csv"}
     * )
     * @Acl(
     *      id="orocrm_contact_import",
     *      name="Import List of Contacts",
     *      description="Import list of contacts",
     *      parent="orocrm_contact"
     * )
     */
    public function importAction()
    {
        $contactData = array (
            'ID' => '69',
            'Name Prefix' => 'Ms.',
            'First Name' => 'April',
            'Last Name' => 'Lynch',
            'Name Suffix' => '',
            'gender' => '',
            'Description' => '',
            'Job Title' => '',
            'Fax' => '',
            'Skype' => '',
            'Twitter' => '',
            'Facebook' => '',
            'GooglePlus' => '',
            'LinkedIn' => '',
            'Birthday' => '1944-08-29T16:52:09+0200',
            'Source' => 'tv',
            'Method' => '',
            'Owner First Name' => 'William',
            'Owner Last Name' => 'Stewart',
            'Assigned To First Name' => 'William',
            'Assigned To Last Name' => 'Stewart',
            'Address Label' => '',
            'Address First Name' => '',
            'Address Last Name' => '',
            'Address Street' => '',
            'Address Street2' => '',
            'Address City' => '',
            'Address State' => '',
            'Address Country' => '',
            'Address Postal Code' => '',
            'Address 1 Label' => '',
            'Address 1 First Name' => '',
            'Address 1 Last Name' => '',
            'Address 1 Street' => '',
            'Address 1 Street2' => '',
            'Address 1 City' => '',
            'Address 1 State' => '',
            'Address 1 Country' => '',
            'Address 1 Postal Code' => '',
            'Address 1 Type' => 'billing',
            'Address 1 Type 1' => 'shipping',
            'Email' => 'primary-email@example.com',
            'Email 1' => 'another-email@example.com',
            'Phone' => '0 800 11 22 444',
            'Phone 1' => '0 800 11 22 555',
            'Group' => 'first_group',
            'Group 1' => 'second_group',
            'Account' => 'First Account Name',
            'Account 1' => 'Second Account Name',
        );

        /** @var ContactDataConverter $contactDataConverter */
        $contactDataConverter = $this->get('orocrm_contact.importexport.data_converter.contact');
        $contactImportData = $contactDataConverter->convertToImportFormat($contactData);

        /** @var Serializer $importExportSerializer */
        $importExportSerializer = $this->get('oro_importexport.serializer');
        $deserializedData = $importExportSerializer->deserialize(
            $contactImportData,
            'OroCRM\Bundle\ContactBundle\Entity\Contact',
            null
        );

        return new Response(
            '<pre>' . print_r($contactImportData, true). "\n\n" . print_r($deserializedData, true) . '</pre>'
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
     * @return ApiEntityManager
     */
    protected function getManager()
    {
        return $this->get('orocrm_contact.contact.manager');
    }
}
