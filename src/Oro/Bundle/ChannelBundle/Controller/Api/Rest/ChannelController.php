<?php

namespace Oro\Bundle\ChannelBundle\Controller\Api\Rest;

use Doctrine\ORM\EntityNotFoundException;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Oro\Bundle\ChannelBundle\Event\ChannelBeforeDeleteEvent;
use Oro\Bundle\ChannelBundle\Event\ChannelDeleteEvent;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
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
     *      description="Number of items per page. Defaults to 10."
     * )
     * @QueryParam(
     *      name="entity",
     *      requirements=".+",
     *      nullable=true,
     *      description="The entity alias. One or several aliases separated by comma. Defaults to all entities"
     * )
     * @QueryParam(
     *      name="active",
     *      requirements="true|false",
     *      nullable=true,
     *      strict=true,
     *      description="The channel active status. Default for all(active, inactive) channel statuses"
     * )
     * @ApiDoc(
     *      description="Get all channels",
     *      resource=true
     * )
     * @AclAncestor("oro_channel_view")
     * @param Request $request
     * @return Response
     */
    public function cgetAction(Request $request)
    {
        $page     = (int)$request->get('page', 1);
        $limit    = (int)$request->get('limit', self::ITEMS_PER_PAGE);
        $entities = $request->get('entity', null);

        $filterParameters = [
            'entity' => new ChainParameterFilter(
                [
                    new StringToArrayParameterFilter(),
                    new EntityClassParameterFilter($this->get('oro_entity.entity_class_name_helper'))
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

        $criteria = $this->getFilterCriteria($this->getSupportedQueryParameters('cgetAction'), $filterParameters, $map);

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
     * @Acl(
     *      id="oro_channel_delete",
     *      type="entity",
     *      permission="DELETE",
     *      class="OroChannelBundle:Channel"
     * )
     * @return Response
     */
    public function deleteAction(int $id)
    {
        try {
            $channel = $this->getManager()->find($id);

            $this->get('event_dispatcher')->dispatch(
                new ChannelBeforeDeleteEvent($channel),
                ChannelBeforeDeleteEvent::EVENT_NAME
            );

            $this->getDeleteHandler()->handleDelete($id, $this->getManager());
            $this->get('event_dispatcher')->dispatch(new ChannelDeleteEvent($channel), ChannelDeleteEvent::EVENT_NAME);
        } catch (EntityNotFoundException $e) {
            return $this->handleView($this->view(null, Response::HTTP_NOT_FOUND));
        } catch (AccessDeniedException $e) {
            return $this->handleView($this->view(['reason' => $e->getMessage()], Response::HTTP_FORBIDDEN));
        }

        return $this->handleView($this->view(null, Response::HTTP_NO_CONTENT));
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        throw new \BadMethodCallException('FormHandler is not available.');
    }

    /**
     * {@inheritdoc}
     */
    public function getManager()
    {
        return $this->get('oro_channel.manager.channel.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getFormHandler()
    {
        throw new \BadMethodCallException('FormHandler is not available.');
    }
}
