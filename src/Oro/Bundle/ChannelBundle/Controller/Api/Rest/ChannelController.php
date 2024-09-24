<?php

namespace Oro\Bundle\ChannelBundle\Controller\Api\Rest;

use Doctrine\ORM\EntityNotFoundException;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Event\ChannelBeforeDeleteEvent;
use Oro\Bundle\ChannelBundle\Event\ChannelDeleteEvent;
use Oro\Bundle\SecurityBundle\Attribute\Acl;
use Oro\Bundle\SecurityBundle\Attribute\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Oro\Bundle\SoapBundle\Request\Parameters\Filter\BooleanParameterFilter;
use Oro\Bundle\SoapBundle\Request\Parameters\Filter\ChainParameterFilter;
use Oro\Bundle\SoapBundle\Request\Parameters\Filter\EntityClassParameterFilter;
use Oro\Bundle\SoapBundle\Request\Parameters\Filter\StringToArrayParameterFilter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * REST API CRUD controller for Channel entity.
 */
class ChannelController extends RestController
{
    /**
     * Get channels.
     *
     * @ApiDoc(
     *      description="Get all channels",
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
        description: 'Number of items per page. Defaults to 10.',
        nullable: true
    )]
    #[QueryParam(
        name: 'entity',
        requirements: '.+',
        description: 'The entity alias. One or several aliases separated by comma. Defaults to all entities',
        nullable: true
    )]
    #[QueryParam(
        name: 'active',
        requirements: 'true|false',
        description: 'The channel active status. Default for all(active, inactive) channel statuses',
        strict: true,
        nullable: true
    )]
    #[AclAncestor('oro_channel_view')]
    public function cgetAction(Request $request)
    {
        $page     = (int)$request->get('page', 1);
        $limit    = (int)$request->get('limit', self::ITEMS_PER_PAGE);
        $entities = $request->get('entity', null);

        $filterParameters = [
            'entity' => new ChainParameterFilter(
                [
                    new StringToArrayParameterFilter(),
                    new EntityClassParameterFilter($this->container->get('oro_entity.entity_class_name_helper'))
                ]
            ),
            'active' => new BooleanParameterFilter(),
        ];
        $map              = [
            'entity' => 'entities.name',
            'active' => 'status',
        ];

        $joins = [];
        if (!empty($entities)) {
            $joins[] = 'entities';
        }

        $criteria = $this->getFilterCriteria(
            $this->getSupportedQueryParameters('cgetAction'),
            $filterParameters,
            $map
        );

        return $this->handleGetListRequest($page, $limit, $criteria, $joins);
    }

    /**
     * REST DELETE
     *
     * @param int $id
     *
     * @ApiDoc(
     *      description="Delete Channel",
     *      resource=true
     * )
     * @return Response
     */
    #[Acl(id: 'oro_channel_delete', type: 'entity', class: Channel::class, permission: 'DELETE')]
    public function deleteAction(int $id)
    {
        try {
            $channel = $this->getManager()->find($id);

            $this->container->get('event_dispatcher')->dispatch(
                new ChannelBeforeDeleteEvent($channel),
                ChannelBeforeDeleteEvent::EVENT_NAME
            );

            $this->getDeleteHandler()->handleDelete($id, $this->getManager());
            $this->container->get('event_dispatcher')->dispatch(
                new ChannelDeleteEvent($channel),
                ChannelDeleteEvent::EVENT_NAME
            );
        } catch (EntityNotFoundException $e) {
            return $this->handleView($this->view(null, Response::HTTP_NOT_FOUND));
        } catch (AccessDeniedException $e) {
            return $this->handleView($this->view(['reason' => $e->getMessage()], Response::HTTP_FORBIDDEN));
        }

        return $this->handleView($this->view(null, Response::HTTP_NO_CONTENT));
    }

    #[\Override]
    public function getForm()
    {
        throw new \BadMethodCallException('FormHandler is not available.');
    }

    #[\Override]
    public function getManager()
    {
        return $this->container->get('oro_channel.manager.channel.api');
    }

    #[\Override]
    public function getFormHandler()
    {
        throw new \BadMethodCallException('FormHandler is not available.');
    }
}
