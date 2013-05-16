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
use Oro\Bundle\DataAuditBundle\Entity\Audit;

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

        $fieldId = new FieldDescription();
        $fieldId->setName('id');
        $fieldId->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_INTEGER,
                'label'       => 'ID',
                'field_name'  => 'id',
                'filter_type' => FilterInterface::TYPE_NUMBER,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => false,
            )
        );
        $this->fieldsCollection->add($fieldId);

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

        $fieldObjectClass = new FieldDescription();
        $fieldObjectClass->setName('objectClass');
        $fieldObjectClass->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_OPTIONS,
                'label'       => 'Object Class',
                'field_name'  => 'objectClass',
                'filter_type' => FilterInterface::TYPE_CHOICE,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
                //'choices' => array(),
                'choices' => $this->getObjectClassOptions(),
                'multiple' => true,
            )
        );
        $this->fieldsCollection->add($fieldObjectClass);

        $fieldObjectId = new FieldDescription();
        $fieldObjectId->setName('objectId');
        $fieldObjectId->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_INTEGER,
                'label'       => 'Object Id',
                'field_name'  => 'objectId',
                'filter_type' => FilterInterface::TYPE_NUMBER,
                'required'    => false,
                'sortable'    => false,
                'filterable'  => false,
                'show_filter' => false,
            )
        );
        $this->fieldsCollection->add($fieldObjectId);

        $fieldUserId = new FieldDescription();
        $fieldUserId->setName('user');
        $fieldUserId->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => 'User',
                'field_name'  => 'user',
                'filter_type' => FilterInterface::TYPE_STRING,
                'required'    => false,
                'sortable'    => false,
                'filterable'  => false,
                'show_filter' => false,
            )
        );
        $this->fieldsCollection->add($fieldUserId);

        $fieldData = new FieldDescription();
        $fieldData->setName('data');
        $fieldData->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_HTML,
                'label'       => 'Data',
                'field_name'  => 'data',
                'filter_type' => FilterInterface::TYPE_STRING,
                'required'    => false,
                'sortable'    => false,
                'filterable'  => false,
                'show_filter' => false,
            )
        );
        $templateProperty = new TwigTemplateProperty($fieldData, 'OroDataAuditBundle:Datagrid:Property/data.html.twig');
        $fieldData->setProperty($templateProperty);
        $this->fieldsCollection->add($fieldData);

        return $this->fieldsCollection;
    }

    /**
     * {@inheritdoc}
     */
    protected function getListFields()
    {
        return $this->getFieldDescriptionCollection()->getElements();
    }

    protected function getObjectClassOptions()
    {
        $options = array();

        $query = $this->createQuery()->getQueryBuilder()
            ->add('select', 'a.objectClass')
            ->add('from', 'Oro\Bundle\DataAuditBundle\Entity\Audit a')
            ->distinct('a.objectClass');

        $result = $query->getQuery()->getArrayResult();

        foreach ((array) $result as $value) {
            $options[$value['objectClass']] = str_replace('Oro\\Bundle\\', '', $value['objectClass']);
        }

        return $options;
    }
}
