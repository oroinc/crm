<?php

namespace Oro\Bundle\AccountBundle\Controller\Api\Rest;

use FOS\RestBundle\Controller\Annotations\QueryParam;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ChannelBundle\Provider\Lifetime\AmountProvider;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;
use Oro\Bundle\SoapBundle\Form\Handler\ApiFormHandler;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * REST API CRUD controller for Account entity.
 */
class AccountController extends RestController
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
     * @AclAncestor("oro_account_view")
     * @param Request $request
     * @return Response
     */
    public function cgetAction(Request $request)
    {
        $page = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit', self::ITEMS_PER_PAGE);

        return $this->handleGetListRequest($page, $limit);
    }

    /**
     * REST GET item
     *
     * @param int $id
     *
     * @ApiDoc(
     *      description="Get account item",
     *      resource=true
     * )
     * @AclAncestor("oro_account_view")
     * @return Response
     */
    public function getAction(int $id)
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
     * @AclAncestor("oro_account_update")
     * @return Response
     */
    public function putAction(int $id)
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
     * @AclAncestor("oro_account_create")
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
     *      id="oro_account_delete",
     *      type="entity",
     *      permission="DELETE",
     *      class="OroAccountBundle:Account"
     * )
     * @return Response
     */
    public function deleteAction(int $id)
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
        return $this->get('oro_account.account.manager.api');
    }

    /**
     * @return FormInterface
     */
    public function getForm()
    {
        return $this->get('oro_account.form.account.api');
    }

    /**
     * @return ApiFormHandler
     */
    public function getFormHandler()
    {
        return $this->get('oro_account.form.handler.account.api');
    }

    /**
     * {@inheritDoc}
     *
     * overriden because of new updateHandler requirements ->process(entity, form, request)
     */
    protected function processForm($entity)
    {
        $this->fixRequestAttributes($entity);

        $result = $this->getFormHandler()->process(
            $entity,
            $this->getForm(),
            $this->get('request_stack')->getCurrentRequest()
        );
        if (\is_object($result) || null === $result) {
            return $result;
        }

        // some form handlers may return true/false rather than saved entity
        return $result ? $entity : null;
    }

    protected function getPreparedItem($entity, $resultFields = [])
    {
        $result = parent::getPreparedItem($entity, $resultFields);

        /** @var AmountProvider $amountProvider  */
        $amountProvider = $this->get('oro_channel.provider.lifetime.amount_provider');

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

        $ap = $this->get('oro_channel.provider.lifetime.amount_provider');
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
