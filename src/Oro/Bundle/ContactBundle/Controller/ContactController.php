<?php

namespace Oro\Bundle\ContactBundle\Controller;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Form\Handler\ContactHandler;
use Oro\Bundle\ContactBundle\Form\Type\ContactType;
use Oro\Bundle\EntityBundle\Tools\EntityRoutingHelper;
use Oro\Bundle\FormBundle\Model\UpdateHandler;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * The controller for Contact entity.
 */
class ContactController extends AbstractController
{
    /**
     * @Route("/view/{id}", name="oro_contact_view", requirements={"id"="\d+"})
     *
     * @Template
     * @Acl(
     *      id="oro_contact_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="OroContactBundle:Contact"
     * )
     */
    public function viewAction(Contact $contact)
    {
        return [
            'entity' => $contact,
        ];
    }

    /**
     * @Route("/info/{id}", name="oro_contact_info", requirements={"id"="\d+"})
     *
     * @Template
     * @AclAncestor("oro_contact_view")
     * @param Request $request
     * @param Contact $contact
     * @return array|RedirectResponse
     */
    public function infoAction(Request $request, Contact $contact)
    {
        if (!$request->get('_wid')) {
            return $this->redirect(
                $this->get(RouterInterface::class)->generate('oro_contact_view', ['id' => $contact->getId()])
            );
        }

        return array(
            'entity'  => $contact
        );
    }

    /**
     * Create contact form
     * @Route("/create", name="oro_contact_create")
     * @Template("@OroContact/Contact/update.html.twig")
     * @Acl(
     *      id="oro_contact_create",
     *      type="entity",
     *      permission="CREATE",
     *      class="OroContactBundle:Contact"
     * )
     * @param Request $request
     * @return array|RedirectResponse
     */
    public function createAction(Request $request)
    {
        // add predefined account to contact
        $contact = null;
        $entityClass = $request->get('entityClass');
        if ($entityClass) {
            $entityClass = $this->get(EntityRoutingHelper::class)->resolveEntityClass($entityClass);
            $entityId = $request->get('entityId');
            if ($entityId && $entityClass === Account::class) {
                $repository = $this->getDoctrine()->getRepository($entityClass);
                /** @var Account $account */
                $account = $repository->find($entityId);
                if ($account) {
                    /** @var Contact $contact */
                    $contact = $this->getManager()->createEntity();
                    $contact->addAccount($account);
                } else {
                    throw new NotFoundHttpException(sprintf('Account with ID %s is not found', $entityId));
                }
            }
        }

        return $this->update($contact);
    }

    /**
     * Update user form
     * @Route("/update/{id}", name="oro_contact_update", requirements={"id"="\d+"})
     *
     * @Template
     * @Acl(
     *      id="oro_contact_update",
     *      type="entity",
     *      permission="EDIT",
     *      class="OroContactBundle:Contact"
     * )
     * @param Contact $entity
     */
    public function updateAction(Contact $entity)
    {
        return $this->update($entity);
    }

    /**
     * @Route(
     *      "/{_format}",
     *      name="oro_contact_index",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     *
     * @Template
     * @AclAncestor("oro_contact_view")
     */
    public function indexAction()
    {
        return [
            'entity_class' => Contact::class
        ];
    }

    /**
     * @return ApiEntityManager
     */
    protected function getManager(): ApiEntityManager
    {
        return $this->get(ApiEntityManager::class);
    }

    protected function update(Contact $entity = null)
    {
        if (!$entity) {
            $entity = $this->getManager()->createEntity();
        }

        return $this->get(UpdateHandler::class)->update(
            $entity,
            $this->get(ContactType::class),
            $this->get(TranslatorInterface::class)->trans('oro.contact.controller.contact.saved.message'),
            $this->get(ContactHandler::class)
        );
    }

    /**
     * @Route("/widget/account-contacts/{id}", name="oro_account_widget_contacts", requirements={"id"="\d+"})
     * @AclAncestor("oro_contact_view")
     * @Template()
     */
    public function accountContactsAction(Account $account)
    {
        $defaultContact = $account->getDefaultContact();
        $contacts = $account->getContacts();
        $contactsWithoutDefault = array();

        if (empty($defaultContact)) {
            $contactsWithoutDefault = $contacts->toArray();
        } else {
            /** @var Contact $contact */
            foreach ($contacts as $contact) {
                if ($contact->getId() == $defaultContact->getId()) {
                    continue;
                }
                $contactsWithoutDefault[] = $contact;
            }
        }

        /**
         * Compare contacts to sort them alphabetically
         *
         * @param Contact $firstContact
         * @param Contact $secondContact
         * @return int
         */
        $compareFunction = function ($firstContact, $secondContact) {
            $first = $firstContact->getLastName() . $firstContact->getFirstName() . $firstContact->getMiddleName();
            $second = $secondContact->getLastName() . $secondContact->getFirstName() . $secondContact->getMiddleName();
            return strnatcasecmp($first, $second);
        };

        usort($contactsWithoutDefault, $compareFunction);

        return array(
            'entity'                 => $account,
            'defaultContact'         => $defaultContact,
            'contactsWithoutDefault' => $contactsWithoutDefault
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices()
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                EntityRoutingHelper::class,
                ApiEntityManager::class,
                UpdateHandler::class,
                ContactType::class,
                TranslatorInterface::class,
                ContactHandler::class,
            ]
        );
    }
}
