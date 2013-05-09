<?php

namespace Oro\Bundle\SoapBundle\Controller\Api\Rest;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\Proxy\Proxy;
use Doctrine\ORM\UnitOfWork;

use FOS\Rest\Util\Codes;
use FOS\RestBundle\Controller\FOSRestController;

use Oro\Bundle\SoapBundle\Form\Handler\ApiFormHandler;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;

abstract class RestController extends FOSRestController
{
    const ITEMS_PER_PAGE = 10;

    /**
     * GET entities list
     *
     * @return Response
     */
    protected function handleGetListRequest()
    {
        $offset = (int)$this->getRequest()->get('page', 1);
        $limit = (int)$this->getRequest()->get('limit', self::ITEMS_PER_PAGE);
        $manager = $this->getManager();
        $items = $manager->getList($limit, $offset);

        $result = null;
        foreach ($items as $item) {
            $result[] = $this->getPreparedItem($item);
        }
        unset($items);

        return new Response(json_encode($result), $result ? Codes::HTTP_OK : Codes::HTTP_NOT_FOUND);
    }

    /**
     * GET single item
     *
     * @param mixed $id
     * @return Response
     */
    public function handleGetRequest($id)
    {
        $item = $this->getManager()->find($id);

        if ($item) {
            $item = $this->getPreparedItem($item);
        }
        return new Response(json_encode($item), $item ? Codes::HTTP_OK : Codes::HTTP_NOT_FOUND);
    }

    /**
     * Edit entity
     *
     * @param mixed $id
     * @return Response
     */
    public function handlePutRequest($id)
    {
        $entity = $this->getManager()->find($id);
        if (!$entity) {
            return $this->handleView($this->view(null, Codes::HTTP_NOT_FOUND));
        }

        $this->fixRequestAttributes($entity);
        $view = $this->getFormHandler()->process($entity)
            ? $this->view(null, Codes::HTTP_NO_CONTENT)
            : $this->view($this->getForm(), Codes::HTTP_BAD_REQUEST);


        return $this->handleView($view);
    }

    /**
     * Create new
     *
     * @return Response
     */
    public function handlePostRequest()
    {
        $entity = $this->getManager()->createEntity();
        $this->fixRequestAttributes($entity);

        $isProcessed = $this->getFormHandler()->process($entity);

        if ($isProcessed) {
            $entityClass = ClassUtils::getRealClass(get_class($entity));
            $classMetadata = $this->getManager()->getObjectManager()->getClassMetadata($entityClass);
            $view = $this->view($classMetadata->getIdentifierValues($entity), Codes::HTTP_CREATED);
        } else {
            $view = $this->view($this->getForm(), Codes::HTTP_BAD_REQUEST);
        }
        return $this->handleView($view);
    }

    /**
     * Delete entity
     *
     * @param mixed $id
     * @return Response
     */
    public function handleDeleteRequest($id)
    {
        $entity = $this->getManager()->find($id);
        if (!$entity) {
            return $this->handleView($this->view(null, Codes::HTTP_NOT_FOUND));
        }

        $em = $this->getManager()->getObjectManager();
        $em->remove($entity);
        $em->flush();

        return $this->handleView($this->view(null, Codes::HTTP_NO_CONTENT));
    }

    /**
     * Prepare entity for serialization
     *
     * @param mixed $entity
     * @return array
     */
    protected function getPreparedItem($entity)
    {
        $result = array();
        /** @var UnitOfWork $uow */
        $uow = $this->getDoctrine()->getManager()->getUnitOfWork();
        foreach ($uow->getOriginalEntityData($entity) as $field => $value) {
            $getter = 'get' . ucfirst($field);
            if (method_exists($entity, $getter)) {
                $value = $entity->$getter();

                $this->transformEntityField($field, $value);
                $result[$field] = $value;
            }
        }
        return $result;
    }

    /**
     * Prepare entity field for serialization
     *
     * @param string $field
     * @param mixed $value
     */
    protected function transformEntityField($field, &$value)
    {
        if ($value instanceof Proxy && method_exists($value, '__toString')) {
            $value = (string)$value;
        } elseif ($value instanceof \DateTime) {
            $value = $value->format('c');
        }
    }

    /**
     * Transform request
     *
     * Assumed post data in the following format:
     * {entity: {"id": "21", "property_one": "Test", "attributes": {"flexible_attribute_code": "John"}}}
     * {entity: {"id": "21", "property_one": "Test", "attributes": {"flexible_attribute_code": {"value": "John", "scope": "mobile"}}}}
     *
     * @param mixed $entity
     */
    protected function fixRequestAttributes($entity)
    {
        return;
    }

    /**
     * Get entity Manager
     *
     * @return ApiEntityManager
     */
    abstract protected function getManager();

    /**
     * @return FormInterface
     */
    abstract protected function getForm();

    /**
     * @return ApiFormHandler
     */
    abstract protected function getFormHandler();
}
