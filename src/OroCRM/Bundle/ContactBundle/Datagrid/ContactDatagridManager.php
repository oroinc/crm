<?php

namespace OroCRM\Bundle\ContactBundle\Datagrid;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\PersistentCollection;

use Oro\Bundle\FlexibleEntityBundle\Entity\Collection;
use Oro\Bundle\FlexibleEntityBundle\Form\Type\PhoneType;
use Oro\Bundle\GridBundle\Datagrid\FlexibleDatagridManager;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Action\ActionInterface;
use Oro\Bundle\GridBundle\Property\UrlProperty;
use Oro\Bundle\GridBundle\Property\CallbackProperty;
use Oro\Bundle\GridBundle\Datagrid\ResultRecordInterface;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;

class ContactDatagridManager extends FlexibleDatagridManager
{
    /**
     * {@inheritDoc}
     */
    protected function getProperties()
    {
        return array(
            new UrlProperty('view_link', $this->router, 'orocrm_contact_view', array('id')),
            new UrlProperty('update_link', $this->router, 'orocrm_contact_update', array('id')),
            new UrlProperty('delete_link', $this->router, 'oro_api_delete_contact', array('id')),
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * {@inheritDoc}
     */
    protected function configureFields(FieldDescriptionCollection $fieldsCollection)
    {
        $fieldId = new FieldDescription();
        $fieldId->setName('id');
        $fieldId->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_INTEGER,
                'label'       => $this->translate('ID'),
                'field_name'  => 'id',
                'filter_type' => FilterInterface::TYPE_NUMBER,
                'show_column' => false
            )
        );
        $fieldsCollection->add($fieldId);

        $this->configureFlexibleField($fieldsCollection, 'first_name');
        $this->configureFlexibleField($fieldsCollection, 'last_name');

        $fieldPhone = new FieldDescription();
        $fieldPhone->setName('office_phone');
        $fieldPhone->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => $this->translate('Phone'),
                'field_name'  => 'phones',
                'filter_type' => FilterInterface::TYPE_STRING,
            )
        );
        $phoneProperty = new CallbackProperty(
            $fieldPhone->getName(),
            function (ResultRecordInterface $record) use ($fieldPhone) {
                try {
                    $phonesValue = $record->getValue($fieldPhone->getFieldName());
                    if ($phonesValue) {
                        $phones = $phonesValue->getData();
                        /** @var $phone Collection */
                        foreach ($phones as $phone) {
                            if ($phone && $phone->getType() == PhoneType::TYPE_OFFICE) {
                                return $phone->getData();
                            }
                        }
                    }
                    return null;
                } catch (\Exception $e) {
                    return null;
                }
            }
        );
        $fieldPhone->setProperty($phoneProperty);
        $fieldsCollection->add($fieldPhone);

        $fieldEmail = new FieldDescription();
        $fieldEmail->setName('email');
        $fieldEmail->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => $this->translate('Email'),
                'field_name'  => 'emails',
                'filter_type' => FilterInterface::TYPE_STRING,
            )
        );
        $emailProperty = new CallbackProperty(
            $fieldEmail->getName(),
            function (ResultRecordInterface $record) use ($fieldEmail) {
                try {
                    $emailsValue = $record->getValue($fieldEmail->getFieldName());
                    if ($emailsValue) {
                        /** @var $emails PersistentCollection */
                        $emails = $emailsValue->getData();
                        if ($emails->count() > 0) {
                            /** @var $email Collection */
                            $email = $emails->first();
                            return $email->getData();
                        }
                    }
                    return null;
                } catch (\Exception $e) {
                    return null;
                }
            }
        );
        $fieldEmail->setProperty($emailProperty);
        $fieldsCollection->add($fieldEmail);

        $fieldCountry = new FieldDescription();
        $fieldCountry->setName('country');
        $fieldCountry->setOptions(
            array(
                'type'            => FieldDescriptionInterface::TYPE_TEXT,
                'label'           => $this->translate('Country'),
                'field_name'      => 'countryName',
                'expression'      => 'address.country',
                'filter_type'     => FilterInterface::TYPE_ENTITY,
                'sortable'        => true,
                'filterable'      => true,
                'show_filter'     => true,
                // entity filter options
                'multiple'        => true,
                'class'           => 'OroAddressBundle:Country',
                'property'        => 'name',
                'filter_by_where' => true,
            )
        );
        $fieldsCollection->add($fieldCountry);

        $fieldCreated = new FieldDescription();
        $fieldCreated->setName('created');
        $fieldCreated->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_DATETIME,
                'label'       => $this->translate('Created At'),
                'field_name'  => 'created',
                'filter_type' => FilterInterface::TYPE_DATETIME,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
            )
        );
        $fieldsCollection->add($fieldCreated);

        $fieldUpdated = new FieldDescription();
        $fieldUpdated->setName('updated');
        $fieldUpdated->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_DATETIME,
                'label'       => $this->translate('Updated At'),
                'field_name'  => 'updated',
                'filter_type' => FilterInterface::TYPE_DATETIME,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
            )
        );
        $fieldsCollection->add($fieldUpdated);
    }

    /**
     * {@inheritdoc}
     */
    protected function getRowActions()
    {
        $clickAction = array(
            'name'         => 'rowClick',
            'type'         => ActionInterface::TYPE_REDIRECT,
            'acl_resource' => 'root',
            'options'      => array(
                'label'         => $this->translate('View'),
                'link'          => 'view_link',
                'runOnRowClick' => true,
            )
        );

        $viewAction = array(
            'name'         => 'view',
            'type'         => ActionInterface::TYPE_REDIRECT,
            'acl_resource' => 'root',
            'options'      => array(
                'label' => $this->translate('View'),
                'icon'  => 'user',
                'link'  => 'view_link',
            )
        );

        $updateAction = array(
            'name'         => 'update',
            'type'         => ActionInterface::TYPE_REDIRECT,
            'acl_resource' => 'root',
            'options'      => array(
                'label'   => $this->translate('Update'),
                'icon'    => 'edit',
                'link'    => 'update_link',
            )
        );

        $deleteAction = array(
            'name'         => 'delete',
            'type'         => ActionInterface::TYPE_DELETE,
            'acl_resource' => 'root',
            'options'      => array(
                'label' => $this->translate('Delete'),
                'icon'  => 'trash',
                'link'  => 'delete_link',
            )
        );

        return array($clickAction, $viewAction, $updateAction, $deleteAction);
    }

    /**
     * @param \Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface $query
     */
    protected function prepareQuery(ProxyQueryInterface $query)
    {
        $entityAlias = $query->getRootAlias();

        /** @var $query QueryBuilder */
        $query->leftJoin("$entityAlias.multiAddress", 'address', 'WITH', 'address.primary = 1')
            ->leftJoin('address.country', 'country');

        $query->addSelect('country.name as countryName', true);
    }
}
