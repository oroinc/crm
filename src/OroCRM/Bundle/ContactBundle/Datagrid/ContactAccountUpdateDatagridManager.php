<?php

namespace OroCRM\Bundle\ContactBundle\Datagrid;

use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\GridBundle\Datagrid\FlexibleDatagridManager;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Sorter\SorterInterface;
use Oro\Bundle\GridBundle\Datagrid\ParametersInterface;

class ContactAccountUpdateDatagridManager extends ContactAccountDatagridManager
{
    /**
     * {@inheritDoc}
     */
    protected function configureFields(FieldDescriptionCollection $fieldsCollection)
    {
        $fieldHasAccount = new FieldDescription();
        $fieldHasAccount->setName('has_contact');
        $fieldHasAccount->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_BOOLEAN,
                'label'       => 'Has contact',
                'field_name'  => 'hasCurrentContact',
                'expression'  => 'hasCurrentContact',
                'nullable'    => false,
                'editable'    => true,
                'sortable'    => true,
                'filter_type' => FilterInterface::TYPE_BOOLEAN,
                'filterable'  => true,
                'show_filter' => true,
            )
        );
        $fieldsCollection->add($fieldHasAccount);

        parent::configureFields($fieldsCollection);
    }

    /**
     * {@inheritDoc}
     */
    protected function createQuery()
    {
        $query = parent::createQuery();

        // remove current contact filter
        $query->resetDQLPart('where');

        if ($this->getContact()->getId()) {
            $query->addSelect(
                'CASE WHEN ' .
                '(:contact MEMBER OF a.contacts OR a.id IN (:data_in)) AND a.id NOT IN (:data_not_in) '.
                'THEN 1 ELSE 0 END AS hasCurrentContact',
                true
            );
        } else {
            $query->addSelect(
                'CASE WHEN ' .
                'a.id IN (:data_in) AND a.id NOT IN (:data_not_in) '.
                'THEN 1 ELSE 0 END AS hasCurrentContact',
                true
            );
        }

        return $query;
    }

    /**
     * {@inheritDoc}
     */
    protected function getDefaultSorters()
    {
        return array(
            'has_contact' => SorterInterface::DIRECTION_DESC,
            'name'        => SorterInterface::DIRECTION_ASC,
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function getQueryParameters()
    {
        $additionalParameters = $this->parameters->get(ParametersInterface::ADDITIONAL_PARAMETERS);
        $dataIn    = !empty($additionalParameters['data_in']) ? $additionalParameters['data_in'] : array(0);
        $dataNotIn = !empty($additionalParameters['data_not_in']) ? $additionalParameters['data_not_in'] : array(0);

        $parameters = array('data_in' => $dataIn, 'data_not_in' => $dataNotIn);

        if ($this->getContact()->getId()) {
            $parameters = array_merge(parent::getQueryParameters(), $parameters);
        }

        return $parameters;
    }
}
