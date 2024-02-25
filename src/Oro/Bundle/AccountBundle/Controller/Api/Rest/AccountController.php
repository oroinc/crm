<?php

namespace Oro\Bundle\AccountBundle\Controller\Api\Rest;

use FOS\RestBundle\Controller\Annotations\QueryParam;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ChannelBundle\Provider\Lifetime\AmountProvider;
use Oro\Bundle\SecurityBundle\Attribute\Acl;
use Oro\Bundle\SecurityBundle\Attribute\AclAncestor;
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
     * @ApiDoc(
     *      description="Get all account items",
     *      resource=true
     * )
     * @param Request $request
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
    #[AclAncestor('oro_account_view')]
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
     * @return Response
     */
    #[AclAncestor('oro_account_view')]
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
     * @return Response
     */
    #[AclAncestor('oro_account_update')]
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
     */
    #[AclAncestor('oro_account_create')]
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
     * @return Response
     */
    #[Acl(id: 'oro_account_delete', type: 'entity', class: Account::class, permission: 'DELETE')]
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
        return $this->container->get('oro_account.account.manager.api');
    }

    /**
     * @return FormInterface
     */
    public function getForm()
    {
        return $this->container->get('oro_account.form.account.api');
    }

    /**
     * @return ApiFormHandler
     */
    public function getFormHandler()
    {
        return $this->container->get('oro_account.form.handler.account.api');
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
            $this->container->get('request_stack')->getCurrentRequest()
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
        $amountProvider = $this->container->get('oro_channel.provider.lifetime.amount_provider');

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

        $ap = $this->container->get('oro_channel.provider.lifetime.amount_provider');
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
