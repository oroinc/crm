<?php

namespace OroCRM\Bundle\AccountBundle\Datagrid;

use Oro\Bundle\GridBundle\Datagrid\FlexibleDatagridManager;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Sorter\SorterInterface;
use Oro\Bundle\GridBundle\Datagrid\ParametersInterface;

class AccountContactUpdateDatagridManager extends AccountContactDatagridManager
{
    /**
     * {@inheritDoc}
     */
    protected function configureFields(FieldDescriptionCollection $fieldsCollection)
    {
        $fieldHasAccount = new FieldDescription();
        $fieldHasAccount->setName('has_account');
        $fieldHasAccount->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_BOOLEAN,
                'label'       => $this->translate('Assigned'),
                'field_name'  => 'hasCurrentAccount',
                'expression'  => 'hasCurrentAccount',
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

        // remove current account filter
        $query->resetDQLPart('where');

        $entityAlias = $query->getRootAlias();

        if ($this->getAccount()->getId()) {
            $query->addSelect(
                "CASE WHEN " .
                "(:account MEMBER OF $entityAlias.accounts OR $entityAlias.id IN (:data_in)) AND " .
                "$entityAlias.id NOT IN (:data_not_in) ".
                "THEN 1 ELSE 0 END AS hasCurrentAccount",
                true
            );
        } else {
            $query->addSelect(
                "CASE WHEN " .
                "$entityAlias.id IN (:data_in) AND $entityAlias.id NOT IN (:data_not_in) ".
                "THEN 1 ELSE 0 END AS hasCurrentAccount",
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
            'has_account' => SorterInterface::DIRECTION_DESC,
            'last_name'   => SorterInterface::DIRECTION_ASC,
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

        if ($this->getAccount()->getId()) {
            $parameters = array_merge(parent::getQueryParameters(), $parameters);
        }

        return $parameters;
    }
}
