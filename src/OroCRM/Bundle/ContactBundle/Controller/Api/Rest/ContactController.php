<?php

namespace OroCRM\Bundle\ContactBundle\Controller\Api\Rest;

use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Routing\ClassResourceInterface;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;
use Oro\Bundle\SoapBundle\Form\Handler\ApiFormHandler;
use Oro\Bundle\SoapBundle\Request\Parameters\Filter\HttpDateTimeParameterFilter;
use Oro\Bundle\SoapBundle\Request\Parameters\Filter\IdentifierToReferenceFilter;

use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\ContactBundle\Form\Type\ContactApiType;

/**
 * @RouteResource("contact")
 * @NamePrefix("oro_api_")
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
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
     *     name="ownerId",
     *     requirements="\d+",
     *     nullable=true,
     *     description="Id of owner user"
     * )
     * @QueryParam(
     *     name="ownerUsername",
     *     requirements=".+",
     *     nullable=true,
     *     description="Username of owner user"
     * )
     * @QueryParam(
     *     name="assigneeId",
     *     requirements="\d+",
     *     nullable=true,
     *     description="Id of assignee"
     * )
     * @QueryParam(
     *     name="assigneeUsername",
     *     requirements=".+",
     *     nullable=true,
     *     description="Username of assignee"
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

        $dateParamFilter  = new HttpDateTimeParameterFilter();
        $userIdFilter     = new IdentifierToReferenceFilter($this->getDoctrine(), 'OroUserBundle:User');
        $userNameFilter   = new IdentifierToReferenceFilter($this->getDoctrine(), 'OroUserBundle:User', 'username');
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
            'assigneeUsername' => 'assignedTo'
        ];

        $criteria = $this->getFilterCriteria($this->getSupportedQueryParameters('cgetAction'), $filterParameters, $map);

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
     * {@inheritDoc}
     */
    protected function processForm($entity)
    {
        $this->fixRequest($entity);
        return parent::processForm($entity);
    }

    /**
     * @param Contact $contact
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
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

        // @todo: just a temporary workaround until new API is implemented
        // - convert country name to country code (as result we accept both the code and the name)
        //   also it will be good to accept ISO3 code in future, need to be discussed with product owners
        // - convert region name to region code (as result we accept the combined code, code and name)
        // - move region name to region_text field for unknown region
        if (array_key_exists('addresses', $contactData)) {
            foreach ($contactData['addresses'] as &$address) {
                if (!empty($address['country'])) {
                    $countryCode = $this->getCountryCodeByName($address['country']);
                    if (!empty($countryCode)) {
                        $address['country'] = $countryCode;
                    }
                }
                if (!empty($address['region']) && !$this->isRegionCombinedCodeByCode($address['region'])) {
                    if (!empty($address['country'])) {
                        $regionId = $this->getRegionCombinedCodeByCode($address['country'], $address['region']);
                        if (!empty($regionId)) {
                            $address['region'] = $regionId;
                        } else {
                            $regionId = $this->getRegionCombinedCodeByName($address['country'], $address['region']);
                            if (!empty($regionId)) {
                                $address['region'] = $regionId;
                            } else {
                                $address['region_text'] = $address['region'];
                                unset($address['region']);
                            }
                        }
                    } else {
                        $address['region_text'] = $address['region'];
                        unset($address['region']);
                    }
                }
            }
            $this->getRequest()->request->set($formAlias, $contactData);
        }
    }

    /**
     * @param string $countryName
     *
     * @return string|null
     */
    protected function getCountryCodeByName($countryName)
    {
        $countryRepo = $this->get('doctrine.orm.entity_manager')
            ->getRepository('OroAddressBundle:Country');
        $country = $countryRepo->createQueryBuilder('c')
            ->select('c.iso2Code')
            ->where('c.name = :name')
            ->setParameter('name', $countryName)
            ->getQuery()
            ->getArrayResult();

        return !empty($country) ? $country[0]['iso2Code'] : null;
    }

    /**
     * @param string $region
     *
     * @return bool
     */
    protected function isRegionCombinedCodeByCode($region)
    {
        $regionRepo = $this->get('doctrine.orm.entity_manager')
            ->getRepository('OroAddressBundle:Region');
        $region = $regionRepo->createQueryBuilder('r')
            ->select('r.combinedCode')
            ->where('r.combinedCode = :region')
            ->setParameter('region', $region)
            ->getQuery()
            ->getArrayResult();

        return !empty($region);
    }

    /**
     * @param string $countryCode
     * @param string $regionCode
     *
     * @return string|null
     */
    protected function getRegionCombinedCodeByCode($countryCode, $regionCode)
    {
        $regionRepo = $this->get('doctrine.orm.entity_manager')
            ->getRepository('OroAddressBundle:Region');
        $region = $regionRepo->createQueryBuilder('r')
            ->select('r.combinedCode')
            ->innerJoin('r.country', 'c')
            ->where('c.iso2Code = :country AND r.code = :region')
            ->setParameter('country', $countryCode)
            ->setParameter('region', $regionCode)
            ->getQuery()
            ->getArrayResult();

        return !empty($region) ? $region[0]['combinedCode'] : null;
    }

    /**
     * @param string $countryCode
     * @param string $regionName
     *
     * @return string|null
     */
    protected function getRegionCombinedCodeByName($countryCode, $regionName)
    {
        $regionRepo = $this->get('doctrine.orm.entity_manager')
            ->getRepository('OroAddressBundle:Region');
        $region = $regionRepo->createQueryBuilder('r')
            ->select('r.combinedCode')
            ->innerJoin('r.country', 'c')
            ->where('c.iso2Code = :country AND r.name = :region')
            ->setParameter('country', $countryCode)
            ->setParameter('region', $regionName)
            ->getQuery()
            ->getArrayResult();

        return !empty($region) ? $region[0]['combinedCode'] : null;
    }

    /**
     * @return string
     */
    protected function getFormAlias()
    {
        return ContactApiType::NAME;
    }

    /**
     * {@inheritDoc}
     */
    protected function fixFormData(array &$data, $entity)
    {
        /** @var Contact $entity */
        parent::fixFormData($data, $entity);

        unset($data['id']);
        unset($data['createdAt']);
        unset($data['updatedAt']);
        unset($data['email']);
        unset($data['createdBy']);
        unset($data['updatedBy']);

        return true;
    }
}
