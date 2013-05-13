<?php

namespace Oro\Bundle\SoapBundle\Controller\Api;

interface ApiCrudInterface
{
    /**
     * Create item.
     *
     * @return mixed
     */
    public function handleCreateRequest();

    /**
     * Get paginated items list.
     *
     * @param int $page
     * @param int $limit
     * @return mixed
     */
    public function handleGetListRequest($page, $limit);

    /**
     * Get item by identifier.
     *
     * @param mixed $id
     * @return mixed
     */
    public function handleGetRequest($id);

    /**
     * Update item.
     *
     * @param mixed $id
     * @return mixed
     */
    public function handleUpdateRequest($id);

    /**
     * Delete item.
     *
     * @param mixed $id
     * @return mixed
     */
    public function handleDeleteRequest($id);
}
