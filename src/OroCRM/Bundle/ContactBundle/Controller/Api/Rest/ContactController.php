<?php

namespace OroCRM\Bundle\ContactBundle\Controller\Api\Rest;

use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Routing\ClassResourceInterface;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;
use Oro\Bundle\SoapBundle\Form\Handler\ApiFormHandler;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\ContactBundle\Entity\ContactAddress;
use OroCRM\Bundle\ContactBundle\Form\Type\ContactApiType;

/**
 * @RouteResource("contact")
 * @NamePrefix("oro_api_")
 */
class ContactController extends RestController implements ClassResourceInterface
{
    /**
     * REST GET list
     *
     * @QueryParam(
     *     name="page", requirements="\d+", nullable=true, description="Page number, starting from 1. Defaults to 1."
     * )
     * @QueryParam(
     *     name="limit", requirements="\d+", nullable=true, description="Number of items per page. defaults to 10."
     * )
     * @QueryParam(
     *     name="createdAt",
     *     requirements="\d{4}(-\d{2}(-\d{2}([T ]\d{2}:\d{2}(:\d{2}(\.\d+)?)?(Z|([-+]\d{2}(:?\d{2})?))?)?)?)?",
     *     nullable=true,
     *     description="Date in RFC 3339 format. For example: 2009-11-05T13:15:30Z, 2008-07-01T22:35:17+08:00"
     * )
     * @QueryParam(
     *     name="updatedAt",
     *     requirements="\d{4}(-\d{2}(-\d{2}([T ]\d{2}:\d{2}(:\d{2}(\.\d+)?)?(Z|([-+]\d{2}(:?\d{2})?))?)?)?)?",
     *     nullable=true,
     *     description="Date in RFC 3339 format. For example: 2009-11-05T13:15:30Z, 2008-07-01T22:35:17+08:00"
     * )
     * @QueryParam(
     *     name="gender", requirements="male|female", nullable=true, description="Gender: male or female"
     * )
     * @ApiDoc(
     *      description="Get all contacts items",
     *      resource=true
     * )
     * @AclAncestor("orocrm_contact_view")
     *
     * @throws \Exception
     * @return Response
     */
    public function cgetAction()
    {
        $page  = (int)$this->getRequest()->get('page', 1);
        $limit = (int)$this->getRequest()->get('limit', self::ITEMS_PER_PAGE);

        $dateClosure = function ($value) {
            // datetime value hack due to the fact that some clients pass + encoded as %20 and not %2B,
            // so it becomes space on symfony side due to parse_str php function in HttpFoundation\Request
            $value = str_replace(' ', '+', $value);

            // The timezone is ignored when DateTime value specifies a timezone (e.g. 2010-01-28T15:00:00+02:00)
            return new \DateTime($value, new \DateTimeZone('UTC'));
        };

        $filterParameters = [
            'createdAt' => [
                'closure' => $dateClosure,
            ],
            'updatedAt' => [
                'closure' => $dateClosure,
            ],
            'gender' => [],
        ];

        $criteria = $this->getFilterCriteria($this->getSupportedQueryParameters('cgetAction'), $filterParameters);

        return $this->handleGetListRequest($page, $limit, $criteria);
    }

    /**
     * REST GET item
     *
     * @param string $id
     *
     * @ApiDoc(
     *      description="Get contact item",
     *      resource=true
     * )
     * @AclAncestor("orocrm_contact_view")
     * @return Response
     */
    public function getAction($id)
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
     * @AclAncestor("orocrm_contact_update")
     * @return Response
     */
    public function putAction($id)
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
     * @AclAncestor("orocrm_contact_create")
     */
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
     * @Acl(
     *      id="orocrm_contact_delete",
     *      type="entity",
     *      permission="DELETE",
     *      class="OroCRMContactBundle:Contact"
     * )
     * @return Response
     */
    public function deleteAction($id)
    {
        return $this->handleDeleteRequest($id);
    }

    /**
     * Get entity Manager
     *
     * @return ApiEntityManager
     */
    public function getManager()
    {
        return $this->get('orocrm_contact.contact.manager.api');
    }

    /**
     * @return FormInterface
     */
    public function getForm()
    {
        return $this->get('orocrm_contact.form.contact.api');
    }

    /**
     * @return ApiFormHandler
     */
    public function getFormHandler()
    {
        return $this->get('orocrm_contact.form.handler.contact.api');
    }

    /**
     * @param Contact $entity
     * @param array $result
     * @return array
     */
    protected function prepareContactEntities(Contact $entity, array $result)
    {
        // use contact source name instead of label
        $source = $entity->getSource();
        if ($source) {
            $result['source'] = $source->getName();
        } else {
            $result['source'] = null;
        }

        // use contact method name instead of label
        $method = $entity->getMethod();
        if ($method) {
            $result['method'] = $method->getName();
        } else {
            $result['method'] = null;
        }

        $result['emails'] = array();
        foreach ($entity->getEmails() as $email) {
            $result['emails'][] = array(
                'email' => $email->getEmail(),
                'primary' => $email->isPrimary()
            );
        }

        $result['phones'] = array();
        foreach ($entity->getPhones() as $phone) {
            $result['phones'][] = array(
                'phone' => $phone->getPhone(),
                'primary' => $phone->isPrimary()
            );
        }

        // set contact group data
        $groupsData = array();
        foreach ($entity->getGroups() as $group) {
            $groupsData[] = parent::getPreparedItem($group);
        }
        $result['groups'] = $groupsData;

        // convert addresses to plain array
        $addressData = array();
        /** @var $address ContactAddress */
        foreach ($entity->getAddresses() as $address) {
            $addressArray = parent::getPreparedItem($address);
            $addressArray['types'] = $address->getTypeNames();
            $addressArray = $this->removeUnusedValues($addressArray, array('owner'));
            $addressData[] = $addressArray;
        }
        $result['addresses'] = $addressData;

        return $result;
    }

    /**
     * @param Contact $entity
     * @param array $result
     * @return array
     */
    protected function prepareExternalEntities(Contact $entity, array $result)
    {
        // set assigned to user data
        $assignedTo = $entity->getAssignedTo();
        if ($assignedTo) {
            $result['assignedTo'] = $assignedTo->getId();
        } else {
            $result['assignedTo'] = null;
        }

        // set owner user data
        $owner = $entity->getOwner();
        if ($owner) {
            $result['owner'] = $owner->getId();
        } else {
            $result['owner'] = null;
        }

        // set reports to contact data
        $reportsTo = $entity->getReportsTo();
        if ($reportsTo) {
            $result['reportsTo'] = $reportsTo->getId();
        } else {
            $result['reportsTo'] = null;
        }

        // convert accounts to plain array
        $accountsIds = array();
        foreach ($entity->getAccounts() as $account) {
            $accountsIds[] = $account->getId();
        }
        $result['accounts'] = $accountsIds;

        // set created and updated users
        $createdBy = $entity->getCreatedBy();
        if ($createdBy) {
            $result['createdBy'] = $createdBy->getId();
        } else {
            $result['createdBy'] = null;
        }

        $updatedBy = $entity->getUpdatedBy();
        if ($updatedBy) {
            $result['updatedBy'] = $updatedBy->getId();
        } else {
            $result['updatedBy'] = null;
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    protected function getPreparedItem($entity, $resultFields = [])
    {
        /** @var Contact $entity */
        $result = parent::getPreparedItem($entity);

        $result = $this->prepareContactEntities($entity, $result);
        $result = $this->prepareExternalEntities($entity, $result);

        return $result;
    }

    /**
     * @param array $data
     * @param array $unusedKeys
     * @return array
     */
    protected function removeUnusedValues(array $data, array $unusedKeys)
    {
        foreach ($unusedKeys as $key) {
            if (array_key_exists($key, $data)) {
                unset($data[$key]);
            }
        }

        return $data;
    }

    /**
     * {@inheritDoc}
     */
    protected function processForm($entity)
    {
        $this->fixRequest($entity);
        return parent::processForm($entity);
    }

    /**
     * @param Contact $contact
     */
    protected function fixRequest($contact)
    {
        $formAlias = $this->getFormAlias();
        $contactData = $this->getRequest()->request->get($formAlias);

        if (array_key_exists('accounts', $contactData)) {
            $accounts = $contactData['accounts'];
            $appendAccounts = array_key_exists('appendAccounts', $contactData)
                ? $contactData['appendAccounts']
                : array();
            $removeAccounts = array_key_exists('removeAccounts', $contactData)
                ? $contactData['removeAccounts']
                : array();

            if ($contact->getId()) {
                foreach ($contact->getAccounts() as $account) {
                    if (!in_array($account->getId(), $accounts)) {
                        $removeAccounts[] = $account->getId();
                    }
                }
            }

            $contactData['appendAccounts'] = array_merge($appendAccounts, $accounts);
            $contactData['removeAccounts'] = $removeAccounts;
            unset($contactData['accounts']);

            $this->getRequest()->request->set($formAlias, $contactData);
        }
    }

    /**
     * @return string
     */
    protected function getFormAlias()
    {
        return ContactApiType::NAME;
    }
}
