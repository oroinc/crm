<?php

namespace Oro\Bundle\SoapBundle\Controller\Api\Rest;

use Doctrine\Common\Util\ClassUtils;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Proxy\Proxy;
use Doctrine\ORM\UnitOfWork;
use FOS\Rest\Util\Codes;
use FOS\RestBundle\Controller\FOSRestController;
use Oro\Bundle\AddressBundle\Form\Handler\ApiFormHandler;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Response;

use Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityAttribute;

abstract class FlexibleRestController extends FOSRestController
{
    /**
     * GET list
     *
     * @return Response
     */
    protected function getListHandler()
    {
        $offset = (int)$this->getRequest()->get('page', 1);
        $limit = (int)$this->getRequest()->get('limit', 10);
        $manager = $this->getManager();
        $items = $manager->getListQuery($limit, $offset);

        $result = null;
        foreach ($items as $item) {
            $result[] = $this->getPreparedItem($item);
        }
        unset($items);

        return new Response(json_encode($result), $result ? Codes::HTTP_OK : Codes::HTTP_NOT_FOUND);
    }

    /**
     * GET item
     *
     * @param string $id
     * @return Response
     */
    public function getHandler($id)
    {
        $manager = $this->getManager();
        $item = $manager->getRepository()->findOneById($id);

        $responseItem = $this->getPreparedItem($item);

        return $this->handleView(
            $this->view($responseItem, $responseItem ? Codes::HTTP_OK : Codes::HTTP_NOT_FOUND)
        );
    }

    /**
     * Edit entity
     *
     * @param int $id
     * @return Response
     */
    public function updateHandler($id)
    {
        $entity = $this->getManager()->getRepository()->findOneById((int)$id);
        if (!$entity) {
            return $this->handleView($this->view(array(), Codes::HTTP_NOT_FOUND));
        }

        $this->fixFlexRequest($entity);
        $view = $this->getFormHandler()->process($entity)
            ? $this->view(array(), Codes::HTTP_NO_CONTENT)
            : $this->view($this->getForm(), Codes::HTTP_BAD_REQUEST);


        return $this->handleView($view);
    }

    /**
     * Create new
     *
     * @return Response
     */
    public function createHandler()
    {
        $entity = $this->getManager()->createFlexible();
        $this->fixFlexRequest($entity);

        $view = $this->getFormHandler()->process($entity)
            ? $this->view(array('id' => $entity->getId()), Codes::HTTP_CREATED)
            : $this->view($this->getForm(), Codes::HTTP_BAD_REQUEST);

        return $this->handleView($view);
    }

    /**
     * Delete entity
     *
     * @param $id
     * @return Response
     */
    public function deleteHandler($id)
    {
        $entity = $this->getManager()->getRepository()->findOneById((int)$id);
        if (!$entity) {
            return $this->handleView($this->view(array(), Codes::HTTP_NOT_FOUND));
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($entity);
        $em->flush();

        return $this->handleView($this->view(array(), Codes::HTTP_NO_CONTENT));
    }

    /**
     * Prepare item for serialization
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
            if ($field == 'values') {
                $result['attributes'] = array();
                foreach ($entity->getValues() as $flexibleValue) {
                    if ($flexibleValue instanceof Proxy) {
                        $flexibleValue->__load();
                    }
                    $attributeValue = $flexibleValue->getData();
                    if ($attributeValue) {
                        $attribute = $flexibleValue->getAttribute();
                        $result['attributes'][$attribute->getCode()] = (object)array(
                            'locale' => $flexibleValue->getLocale(),
                            'scope' => $flexibleValue->getScope(),
                            'value' => $attributeValue
                        );
                    }
                }
                continue;
            }
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
     * @param string $field
     * @param mixed $value
     */
    protected function transformEntityField($field, &$value)
    {
        if ($value instanceof Proxy && method_exists($value, '__toString')) {
            $value = $value->__toString();
        } elseif ($value instanceof \DateTime) {
            $value = $value->format('c');
        }
    }

    /**
     * This is temporary fix for flexible entity values processing.
     *
     * Assumed that user will post data in the following format:
     * {entity: {"id": "21", "property_one": "Test", "attributes": {"flexible_attribute_code": "John"}}}
     * {entity: {"id": "21", "property_one": "Test", "attributes": {"flexible_attribute_code": {"value": "John", "scope": "mobile"}}}}
     *
     * @param mixed $entity
     */
    protected function fixFlexRequest($entity)
    {
        $request = $this->getRequest()->request;
        $data = $request->get($this->getRequestVar(), array());

        $entityClass = ClassUtils::getRealClass(get_class($entity));
        $attrDef = $this->getManager()->getAttributeRepository()->findBy(array('entityType' => $entityClass));
        $attrVal = isset($data['attributes']) ? $data['attributes'] : array();

        unset($data['attributes']);
        $data['values'] = array();

        foreach ($attrDef as $i => $attr) {
            /* @var AbstractEntityAttribute $attr */
            if ($attr->getBackendType() == 'options') {
                if (in_array(
                    $attr->getAttributeType(),
                    array(
                        'oro_flexibleentity_multiselect',
                        'oro_flexibleentity_multicheckbox',
                    )
                )) {
                    $type = 'options';
                    $default = array($attr->getOptions()->offsetGet(0)->getId());
                } else {
                    $type = 'option';
                    $default = $attr->getOptions()->offsetGet(0)->getId();
                }
            } else {
                $type = $attr->getBackendType();
                $default = null;
            }

            $data['values'][$i] = array();
            $data['values'][$i]['id'] = $attr->getId();
            $data['values'][$i][$type] = $default;

            foreach ($attrVal as $fieldCode => $fieldValue) {
                if ($attr->getCode() == (string)$fieldCode) {
                    if (is_array($fieldValue)) {
                        $data['values'][$i]['scope'] = $fieldValue['scope'];
                        $data['values'][$i]['locale'] = $fieldValue['locale'];
                        $fieldValue = $fieldValue['value'];
                    }
                    $data['values'][$i][$type] = (string)$fieldValue;

                    break;
                }
            }
        }

        $request->set($this->getRequestVar(), $data);
    }

    /**
     * Get entity Manager
     *
     * @return FlexibleManager
     */
    abstract protected function getManager();

    /**
     * @return Form
     */
    abstract protected function getForm();

    /**
     * @return ApiFormHandler
     */
    abstract protected function getFormHandler();

    /**
     * @return string
     */
    abstract protected function getRequestVar();
}
