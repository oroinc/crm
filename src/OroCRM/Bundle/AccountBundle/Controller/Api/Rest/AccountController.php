<?php

namespace OroCRM\Bundle\AccountBundle\Controller\Api\Rest;

use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Routing\ClassResourceInterface;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use Oro\Bundle\SoapBundle\Form\Handler\ApiFormHandler;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;

use OroCRM\Bundle\ChannelBundle\Provider\Lifetime\AmountProvider;
use OroCRM\Bundle\AccountBundle\Entity\Account;

/**
 * @RouteResource("account")
 * @NamePrefix("oro_api_")
 */
class AccountController extends RestController implements ClassResourceInterface
{
    /**
     * REST GET list
     *
     * @QueryParam(
     *      name="page",
     *      requirements="\d+",
     *      nullable=true,
     *      description="Page number, starting from 1. Defaults to 1."
     * )
     * @QueryParam(
     *      name="limit",
     *      requirements="\d+",
     *      nullable=true,
     *      description="Number of items per page. defaults to 10."
     * )
     * @ApiDoc(
     *      description="Get all account items",
     *      resource=true
     * )
     * @AclAncestor("orocrm_account_view")
     * @return Response
     */
    public function cgetAction()
    {
        $page = (int)$this->getRequest()->get('page', 1);
        $limit = (int)$this->getRequest()->get('limit', self::ITEMS_PER_PAGE);

        return $this->handleGetListRequest($page, $limit);
    }

    /**
     * REST GET item
     *
     * @param string $id
     *
     * @ApiDoc(
     *      description="Get account item",
     *      resource=true
     * )
     * @AclAncestor("orocrm_account_view")
     * @return Response
     */
    public function getAction($id)
    {
        return $this->handleGetRequest($id);
    }

    /**
     * REST PUT
     *
     * @param int $id Account item id
     *
     * @ApiDoc(
     *      description="Update account",
     *      resource=true
     * )
     * @AclAncestor("orocrm_account_update")
     * @return Response
     */
    public function putAction($id)
    {
        return $this->handleUpdateRequest($id);
    }

    /**
     * Create new account
     *
     * @ApiDoc(
     *      description="Create new account",
     *      resource=true
     * )
     * @AclAncestor("orocrm_account_create")
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
     *      description="Delete Account",
     *      resource=true
     * )
     * @Acl(
     *      id="orocrm_account_delete",
     *      type="entity",
     *      permission="DELETE",
     *      class="OroCRMAccountBundle:Account"
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
        return $this->get('orocrm_account.account.manager.api');
    }

    /**
     * @return FormInterface
     */
    public function getForm()
    {
        return $this->get('orocrm_account.form.account.api');
    }

    /**
     * @return ApiFormHandler
     */
    public function getFormHandler()
    {
        return $this->get('orocrm_account.form.handler.account.api');
    }

    protected function getPreparedItem($entity, $resultFields = [])
    {
        $result = parent::getPreparedItem($entity, $resultFields);

        /** @var AmountProvider $amountProvider  */
        $amountProvider = $this->get('orocrm_channel.provider.lifetime.amount_provider');

        $result['lifetimeValue'] = $amountProvider->getAccountLifeTimeValue($entity);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    protected function getPreparedItems($entities, $resultFields = [])
    {
        $result = [];
        $ids = array_map(
            function (Account $account) {
                return $account->getId();
            },
            $entities
        );

        $ap = $this->get('orocrm_channel.provider.lifetime.amount_provider');
        $lifetimeValues = $ap->getAccountsLifetimeQueryBuilder($ids)
            ->getQuery()
            ->getArrayResult();
        $lifetimeMap = [];
        foreach ($lifetimeValues as $value) {
            $lifetimeMap[$value['accountId']] = (float)$value['lifetimeValue'];
        }

        foreach ($entities as $entity) {
            /** @var Account $entity */
            $entityArray = parent::getPreparedItem($entity, $resultFields);
            if (array_key_exists($entity->getId(), $lifetimeMap)) {
                $entityArray['lifetimeValue'] = $lifetimeMap[$entity->getId()];
            } else {
                $entityArray['lifetimeValue'] = 0.0;
            }

            $result[] = $entityArray;
        }

        return $result;
    }
}
