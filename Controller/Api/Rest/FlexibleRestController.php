<?php

namespace Oro\Bundle\SoapBundle\Controller\Api\Rest;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Proxy\Proxy;
use Doctrine\ORM\UnitOfWork;

use FOS\Rest\Util\Codes;
use FOS\RestBundle\Controller\FOSRestController;

use Oro\Bundle\SoapBundle\Form\Handler\ApiFormHandler;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;
use Oro\Bundle\FlexibleEntityBundle\Entity\Attribute;
use Oro\Bundle\FlexibleEntityBundle\Model\Behavior\ScopableInterface;
use Oro\Bundle\FlexibleEntityBundle\Model\Behavior\TranslatableInterface;
use Oro\Bundle\FlexibleEntityBundle\Model\FlexibleValueInterface;
use Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityAttribute;

abstract class FlexibleRestController extends FOSRestController
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
        $items = $manager->getListQuery($limit, $offset);

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
        $manager = $this->getManager();
        $item = $manager->getRepository()->find($id);

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
        $entity = $this->getManager()->getRepository()->find($id);
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
        $entity = $this->getManager()->getFlexibleManager()->createFlexible();
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
        $entity = $this->getManager()->getRepository()->find($id);
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
            if ($field == 'values') {
                $result['attributes'] = array();
                /** @var FlexibleValueInterface $flexibleValue */
                foreach ($entity->getValues() as $flexibleValue) {
                    if ($flexibleValue instanceof Proxy) {
                        /** @var Proxy $flexibleValue */
                        $flexibleValue->__load();
                    }
                    $attributeValue = $flexibleValue->getData();
                    if ($attributeValue) {
                        /** @var Attribute $attribute */
                        $attribute = $flexibleValue->getAttribute();
                        $attributeData = array('value' => $attributeValue);
                        if ($attributeValue instanceof TranslatableInterface) {
                            /** @var TranslatableInterface $flexibleValue */
                            $attributeData['locale'] = $flexibleValue->getLocale();
                        }
                        if ($attributeValue instanceof ScopableInterface) {
                            /** @var ScopableInterface $flexibleValue */
                            $attributeData['scope'] = $flexibleValue->getScope();
                        }
                        $result['attributes'][$attribute->getCode()] = (object)$attributeData;
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
     * Transform request with flexible entities
     *
     * Assumed post data in the following format:
     * {entity: {"id": "21", "property_one": "Test", "attributes": {"flexible_attribute_code": "John"}}}
     * {entity: {"id": "21", "property_one": "Test", "attributes": {"flexible_attribute_code": {"value": "John", "scope": "mobile"}}}}
     *
     * @param mixed $entity
     */
    protected function fixRequestAttributes($entity)
    {
        $request = $this->getRequest()->request;
        $requestVariable = $this->getForm()->getName();
        $data = $request->get($requestVariable, array());

        /** @var ObjectRepository $attrRepository */
        $attrRepository = $this->getManager()
            ->getFlexibleManager()
            ->getAttributeRepository();
        $entityClass = ClassUtils::getRealClass(get_class($entity));
        $attrDef = $attrRepository->findBy(array('entityType' => $entityClass));
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
                        if (array_key_exists('scope', $fieldValue)) {
                            $data['values'][$i]['scope'] = $fieldValue['scope'];
                        }
                        if (array_key_exists('locale', $fieldValue)) {
                            $data['values'][$i]['locale'] = $fieldValue['locale'];
                        }
                        $fieldValue = $fieldValue['value'];
                    }
                    $data['values'][$i][$type] = (string)$fieldValue;

                    break;
                }
            }
        }

        $request->set($requestVariable, $data);
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
