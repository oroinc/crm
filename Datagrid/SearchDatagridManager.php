<?php

namespace Oro\Bundle\SearchBundle\Datagrid;

use Oro\Bundle\GridBundle\Datagrid\DatagridManager;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\SearchBundle\Query\Query;
use Oro\Bundle\SearchBundle\Engine\Indexer;

class SearchDatagridManager extends DatagridManager
{
    /**
     * @var string
     */
    protected $searchEntity = '*';

    /**
     * @var string
     */
    protected $searchString;

    /**
     * Configure collection of field descriptions
     *
     * @param FieldDescriptionCollection $fieldCollection
     */
    protected function configureFields(FieldDescriptionCollection $fieldCollection)
    {
        $description = new FieldDescription();
        $description->setName('description');
        $description->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_HTML,
                'label'       => '',
                'field_name'  => 'recordText',
                'required'    => false,
                'sortable'    => false,
                'filterable'  => false,
                'show_filter' => false,
            )
        );
        $fieldCollection->add($description);
    }

    /**
     * @return ProxyQueryInterface
     */
    protected function createQuery()
    {
        /** @var $query Query */
        $query = parent::createQuery();
        $query
            ->from($this->searchEntity)
            ->andWhere(Indexer::TEXT_ALL_DATA_FIELD, '~', $this->searchString, 'text');

        return $query;
    }

    /**
     * @return array
     */
    protected function getFilters()
    {
        return array();
    }

    /**
     * @return array
     */
    protected function getSorters()
    {
        return array();
    }

    /**
     * @return array
     */
    protected function getDefaultParameters()
    {
        return array();
    }

    /**
     * Set search entity (f.e. user, product etc.)
     *
     * @param string|null $searchEntity
     */
    public function setSearchEntity($searchEntity)
    {
        if ($searchEntity) {
            $this->searchEntity = $searchEntity;
        } else {
            $this->searchEntity = '*';
        }
    }

    /**
     * Set search string
     *
     * @param string $searchString
     */
    public function setSearchString($searchString)
    {
        $this->searchString = $searchString;
    }
}
