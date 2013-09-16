<?php

namespace OroCRM\Bundle\AccountBundle\Datagrid;

use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Sorter\SorterInterface;
use Oro\Bundle\GridBundle\Datagrid\ParametersInterface;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Datagrid\ORM\QueryFactory\EntityQueryFactory;

class AccountContactUpdateDatagridManager extends AccountContactDatagridManager
{
    /**
     * @var array
     */
    public $additionalParameters = array();

    /**
     * @var string
     */
    protected $hasAccountExpression;

    /**
     * @param array $parameters
     */
    public function setAdditionalParameters(array $parameters)
    {
        $this->additionalParameters = $parameters;
    }

    protected function getDefaultParameters()
    {
        $parameters = parent::getDefaultParameters();
        $parameters[ParametersInterface::ADDITIONAL_PARAMETERS] = $this->additionalParameters;
        return $parameters;
    }

    /**
     * {@inheritDoc}
     */
    protected function configureFields(FieldDescriptionCollection $fieldsCollection)
    {
        $fieldHasAccount = new FieldDescription();
        $fieldHasAccount->setName('has_account');
        $fieldHasAccount->setOptions(
            array(
                'type'            => FieldDescriptionInterface::TYPE_BOOLEAN,
                'label'           => $this->translate('Assigned'),
                'field_name'      => 'hasCurrentAccount',
                'expression'      => $this->getHasAccountExpression(),
                'nullable'        => false,
                'editable'        => true,
                'sortable'        => true,
                'filter_type'     => FilterInterface::TYPE_BOOLEAN,
                'filterable'      => true,
                'show_filter'     => true,
                'filter_by_where' => true,
            )
        );
        $fieldsCollection->add($fieldHasAccount);

        parent::configureFields($fieldsCollection);
    }

    /**
     * @param FieldDescriptionCollection $fieldsCollection
     */
    protected function updateFieldsConfiguration(FieldDescriptionCollection $fieldsCollection)
    {
        // remove unused fields
        foreach (array('groups', 'source', 'created', 'updated') as $fieldName) {
            $fieldsCollection->remove($fieldName);
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function prepareQuery(ProxyQueryInterface $query)
    {
        $this->applyJoinWithAddressAndCountry($query);

        $query->addSelect($this->getHasAccountExpression() . ' AS hasCurrentAccount', true);
    }

    /**
     * @return string
     */
    protected function getHasAccountExpression()
    {
        if (null === $this->hasAccountExpression) {
            /** @var EntityQueryFactory $queryFactory */
            $queryFactory = $this->queryFactory;
            $entityAlias = $queryFactory->getAlias();

            if ($this->getAccount()->getId()) {
                $this->hasAccountExpression =
                    "CASE WHEN " .
                    "(:account MEMBER OF $entityAlias.accounts OR $entityAlias.id IN (:data_in)) AND " .
                    "$entityAlias.id NOT IN (:data_not_in) ".
                    "THEN true ELSE false END";
            } else {
                $this->hasAccountExpression =
                    "CASE WHEN " .
                    "$entityAlias.id IN (:data_in) AND $entityAlias.id NOT IN (:data_not_in) ".
                    "THEN true ELSE false END";
            }
        }

        return $this->hasAccountExpression;
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
