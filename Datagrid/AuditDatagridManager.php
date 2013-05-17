<?php

namespace Oro\Bundle\DataAuditBundle\Datagrid;

use Doctrine\ORM\Query;
use Gedmo\Loggable\LoggableListener;

use Oro\Bundle\GridBundle\Datagrid\DatagridManager;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Property\TwigTemplateProperty;

class AuditDatagridManager extends DatagridManager
{
    /**
     * @var FieldDescriptionCollection
     */
    protected $fieldsCollection;

    /**
     * @return FieldDescriptionCollection
     */
    protected function getFieldDescriptionCollection()
    {
        $this->fieldsCollection = new FieldDescriptionCollection();

        $fieldAction = new FieldDescription();
        $fieldAction->setName('action');
        $fieldAction->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_OPTIONS,
                'label'       => 'Action',
                'field_name'  => 'action',
                'filter_type' => FilterInterface::TYPE_CHOICE,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
                'choices' => array(
                    LoggableListener::ACTION_UPDATE => 'Updated',
                    LoggableListener::ACTION_CREATE => 'Created',
                    LoggableListener::ACTION_REMOVE => 'Deleted',
                ),
                'multiple' => true,
            )
        );
        $this->fieldsCollection->add($fieldAction);

        $fieldVersion = new FieldDescription();
        $fieldVersion->setName('version');
        $fieldVersion->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_INTEGER,
                'label'       => 'Version',
                'field_name'  => 'version',
                'filter_type' => FilterInterface::TYPE_NUMBER,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => false,
            )
        );
        $this->fieldsCollection->add($fieldVersion);

        $fieldObjectClass = new FieldDescription();
        $fieldObjectClass->setName('objectClass');
        $fieldObjectClass->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_OPTIONS,
                'label'       => 'Entity Type',
                'field_name'  => 'objectClass',
                'filter_type' => FilterInterface::TYPE_CHOICE,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
                'choices' => $this->getObjectClassOptions(),
                'multiple' => true,
            )
        );
        $this->fieldsCollection->add($fieldObjectClass);

        $fieldObjectName = new FieldDescription();
        $fieldObjectName->setName('objectName');
        $fieldObjectName->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => 'Entity Name',
                'field_name'  => 'objectName',
                'filter_type' => FilterInterface::TYPE_STRING,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
            )
        );
        $this->fieldsCollection->add($fieldObjectName);

        $fieldObjectId = new FieldDescription();
        $fieldObjectId->setName('objectId');
        $fieldObjectId->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_INTEGER,
                'label'       => 'Entity Id',
                'field_name'  => 'objectId',
                'filter_type' => FilterInterface::TYPE_NUMBER,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
            )
        );
        $this->fieldsCollection->add($fieldObjectId);

        $fieldData = new FieldDescription();
        $fieldData->setName('data');
        $fieldData->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_HTML,
                'label'       => 'Data',
                'field_name'  => 'data',
                'filter_type' => FilterInterface::TYPE_STRING,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
            )
        );
        $templateDataProperty = new TwigTemplateProperty($fieldData, 'OroDataAuditBundle:Datagrid:Property/data.html.twig');
        $fieldData->setProperty($templateDataProperty);
        $this->fieldsCollection->add($fieldData);

        $fieldAuthor = new FieldDescription();
        $fieldAuthor->setName('user');
        $fieldAuthor->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => 'Author',
                'field_name'  => 'user',
                'filter_type' => FilterInterface::TYPE_STRING,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
            )
        );
        $templateAuthorProperty = new TwigTemplateProperty($fieldAuthor, 'OroDataAuditBundle:Datagrid:Property/author.html.twig');
        $fieldAuthor->setProperty($templateAuthorProperty);
        $this->fieldsCollection->add($fieldAuthor);

        $fieldLogged = new FieldDescription();
        $fieldLogged->setName('logged');
        $fieldLogged->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_DATETIME,
                'label'       => 'Logged At',
                'field_name'  => 'loggedAt',
                'filter_type' => FilterInterface::TYPE_DATETIME,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => false,
            )
        );
        $this->fieldsCollection->add($fieldLogged);

        return $this->fieldsCollection;
    }

    /**
     * Traverse all flexible attributes and add them as fields to collection
     *
     * @param FieldDescriptionCollection $fieldsCollection
     * @param array $options
     */
    protected function configureFlexibleFields(
        FieldDescriptionCollection $fieldsCollection,
        array $options = array()
    ) {
        foreach ($this->getFlexibleAttributes() as $attribute) {
            $attributeCode = $attribute->getCode();
            $fieldsCollection->add(
                $this->createFlexibleField(
                    $attribute,
                    isset($options[$attributeCode]) ? $options[$attributeCode] : array()
                )
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getListFields()
    {
        return $this->getFieldDescriptionCollection()->getElements();
    }

    /**
     * @return ProxyQueryInterface
     */
    protected function createQuery()
    {
        $query = parent::createQuery();

//        $query->getQueryBuilder()
//            ->addSelect(
//                'CONCAT(
//                    CONCAT(
//                        CONCAT(u.firstName, \' \'),
//                        CONCAT(u.lastName, \' \')
//                    ),
//                    CONCAT(\' - \', u.email)
//                ) AS author'
//            )
//            ->leftJoin('o.user', 'u');

        //print_r (($query->getQueryBuilder()->getQuery()->getSQL())); die;
        return $query;
    }

    /**
     * Get distinct object classes
     *
     * @return array
     */
    protected function getObjectClassOptions()
    {
        $options = array();

        $query = $this->createQuery()->getQueryBuilder()
            ->add('select', 'a.objectClass')
            ->add('from', 'Oro\Bundle\DataAuditBundle\Entity\Audit a')
            ->distinct('a.objectClass');

        $result = $query->getQuery()->getArrayResult();

        foreach ((array)$result as $value) {
            $options[$value['objectClass']] = current(array_reverse(explode('\\', $value['objectClass'])));
        }

        return $options;
    }
}
