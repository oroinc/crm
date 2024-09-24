<?php

namespace Oro\Bundle\ContactBundle\Controller\Api\Rest;

use Doctrine\Persistence\ManagerRegistry;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Oro\Bundle\AddressBundle\Utils\AddressApiUtils;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Form\Type\ContactApiType;
use Oro\Bundle\SecurityBundle\Attribute\Acl;
use Oro\Bundle\SecurityBundle\Attribute\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;
use Oro\Bundle\SoapBundle\Form\Handler\ApiFormHandler;
use Oro\Bundle\SoapBundle\Request\Parameters\Filter\HttpDateTimeParameterFilter;
use Oro\Bundle\SoapBundle\Request\Parameters\Filter\IdentifierToReferenceFilter;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * REST API CRUD controller for Contact entity.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class ContactController extends RestController
{
    /**
     * REST GET list
     *
     * @ApiDoc(
     *      description="Get all contacts items",
     *      resource=true
     * )
     *
     * @param Request $request
     * @throws \Exception
     * @return Response
     */
    #[QueryParam(
        name: 'page',
        requirements: '\d+',
        description: 'Page number, starting from 1. Defaults to 1.',
        nullable: true
    )]
    #[QueryParam(
        name: 'limit',
        requirements: '\d+',
        description: 'Number of items per page. defaults to 10.',
        nullable: true
    )]
    #[QueryParam(
        name: 'createdAt',
        requirements: '\d{4}(-\d{2}(-\d{2}([T ]\d{2}:\d{2}(:\d{2}(\.\d+)?)?(Z|([-+]\d{2}(:?\d{2})?))?)?)?)?',
        description: 'Date in RFC 3339 format. For example: 2009-11-05T13:15:30Z, 2008-07-01T22:35:17+08:00',
        nullable: true
    )]
    #[QueryParam(
        name: 'updatedAt',
        requirements: '\d{4}(-\d{2}(-\d{2}([T ]\d{2}:\d{2}(:\d{2}(\.\d+)?)?(Z|([-+]\d{2}(:?\d{2})?))?)?)?)?',
        description: 'Date in RFC 3339 format. For example: 2009-11-05T13:15:30Z, 2008-07-01T22:35:17+08:00',
        nullable: true
    )]
    #[QueryParam(name: 'ownerId', requirements: '\d+', description: 'Id of owner user', nullable: true)]
    #[QueryParam(name: 'ownerUsername', requirements: '.+', description: 'Username of owner user', nullable: true)]
    #[QueryParam(name: 'phone', requirements: '.+', description: 'Phone number of contact', nullable: true)]
    #[QueryParam(name: 'assigneeId', requirements: '\d+', description: 'Id of assignee', nullable: true)]
    #[QueryParam(name: 'assigneeUsername', requirements: '.+', description: 'Username of assignee', nullable: true)]
    #[AclAncestor('oro_contact_view')]
    public function cgetAction(Request $request)
    {
        $page  = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit', self::ITEMS_PER_PAGE);

        $dateParamFilter  = new HttpDateTimeParameterFilter();
        $userIdFilter = new IdentifierToReferenceFilter($this->container->get('doctrine'), User::class);
        $userNameFilter = new IdentifierToReferenceFilter($this->container->get('doctrine'), User::class, 'username');

        $filterParameters = [
            'createdAt'        => $dateParamFilter,
            'updatedAt'        => $dateParamFilter,
            'ownerId'          => $userIdFilter,
            'ownerUsername'    => $userNameFilter,
            'assigneeId'       => $userIdFilter,
            'assigneeUsername' => $userNameFilter,
        ];
        $map              = [
            'ownerId'          => 'owner',
            'ownerUsername'    => 'owner',
            'assigneeId'       => 'assignedTo',
            'assigneeUsername' => 'assignedTo',
            'phone'            => 'phones.phone'
        ];
        $joins            = [
            'phones'
        ];
        $criteria = $this->getFilterCriteria($this->getSupportedQueryParameters('cgetAction'), $filterParameters, $map);

        return $this->handleGetListRequest($page, $limit, $criteria, $joins);
    }

    /**
     * REST GET item
     *
     * @param int $id
     *
     * @ApiDoc(
     *      description="Get contact item",
     *      resource=true
     * )
     * @return Response
     */
    #[AclAncestor('oro_contact_view')]
    public function getAction(int $id)
    {
        return $this->handleGetRequest($id);
    }

    /**
     * REST PUT
     *
     * @param int $id Contact item id
     *
     * @ApiDoc(
     *      description="Update contact",
     *      resource=true
     * )
     * @return Response
     */
    #[AclAncestor('oro_contact_update')]
    public function putAction(int $id)
    {
        return $this->handleUpdateRequest($id);
    }

    /**
     * Create new contact
     *
     * @ApiDoc(
     *      description="Create new contact",
     *      resource=true
     * )
     */
    #[AclAncestor('oro_contact_create')]
    public function postAction()
    {
        return $this->handleCreateRequest();
    }

    /**
     * REST DELETE
     *
     * @param int $id
     *
     * @ApiDoc(
     *      description="Delete Contact",
     *      resource=true
     * )
     * @return Response
     */
    #[Acl(id: 'oro_contact_delete', type: 'entity', class: Contact::class, permission: 'DELETE')]
    public function deleteAction(int $id)
    {
        return $this->handleDeleteRequest($id);
    }

    /**
     * Get entity Manager
     *
     * @return ApiEntityManager
     */
    #[\Override]
    public function getManager()
    {
        return $this->container->get('oro_contact.contact.manager.api');
    }

    /**
     * @return FormInterface
     */
    #[\Override]
    public function getForm()
    {
        return $this->container->get('oro_contact.form.contact.api');
    }

    /**
     * @return ApiFormHandler
     */
    #[\Override]
    public function getFormHandler()
    {
        return $this->container->get('oro_contact.form.handler.contact.api');
    }

    #[\Override]
    protected function processForm($entity)
    {
        $this->fixRequestAttributes($entity);

        $result = $this->getFormHandler()->process(
            $entity,
            $this->getForm(),
            $this->container->get('request_stack')->getCurrentRequest()
        );
        if (\is_object($result) || null === $result) {
            return $result;
        }

        // some form handlers may return true/false rather than saved entity
        return $result ? $entity : null;
    }

    /**
     * @param Contact $entity
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    #[\Override]
    protected function fixRequestAttributes($entity)
    {
        $formAlias = $this->getFormAlias();
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $contactData = $request->request->all($formAlias);

        if (array_key_exists('accounts', $contactData)) {
            $accounts = $contactData['accounts'];
            $appendAccounts = array_key_exists('appendAccounts', $contactData)
                ? $contactData['appendAccounts']
                : array();
            $removeAccounts = array_key_exists('removeAccounts', $contactData)
                ? $contactData['removeAccounts']
                : array();

            if ($entity->getId()) {
                foreach ($entity->getAccounts() as $account) {
                    if (!in_array($account->getId(), $accounts)) {
                        $removeAccounts[] = $account->getId();
                    }
                }
            }

            $contactData['appendAccounts'] = array_merge($appendAccounts, $accounts);
            $contactData['removeAccounts'] = $removeAccounts;
            unset($contactData['accounts']);

            $request->request->set($formAlias, $contactData);
        }

        // - convert country name to country code (as result we accept both the code and the name)
        //   also it will be good to accept ISO3 code in future, need to be discussed with product owners
        // - convert region name to region code (as result we accept the combined code, code and name)
        // - move region name to region_text field for unknown region
        if (array_key_exists('addresses', $contactData)) {
            foreach ($contactData['addresses'] as &$address) {
                AddressApiUtils::fixAddress($address, $this->container->get('doctrine.orm.entity_manager'));
            }
            $request->request->set($formAlias, $contactData);
        }

        parent::fixRequestAttributes($entity);
    }

    /**
     * @return string
     */
    protected function getFormAlias()
    {
        return ContactApiType::NAME;
    }

    #[\Override]
    protected function fixFormData(array &$data, $entity)
    {
        /** @var Contact $entity */
        parent::fixFormData($data, $entity);

        unset($data['id']);
        unset($data['updatedAt']);
        unset($data['email']);
        unset($data['createdBy']);
        unset($data['updatedBy']);

        return true;
    }

    #[\Override]
    public static function getSubscribedServices(): array
    {
        return array_merge(
            parent::getSubscribedServices(),
            ['doctrine' => ManagerRegistry::class]
        );
    }
}
