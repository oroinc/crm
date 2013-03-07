<?php

namespace Oro\Bundle\GridBundle\Datagrid;

use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\ValidatorInterface;

use Oro\Bundle\GridBundle\Builder\DatagridBuilderInterface;
use Oro\Bundle\GridBundle\Builder\ListBuilderInterface;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Datagrid\ParametersInterface;
use Oro\Bundle\GridBundle\Route\RouteGeneratorInterface;

abstract class DatagridManager implements DatagridManagerInterface
{
    /**
     * @var DatagridBuilderInterface
     */
    protected $datagridBuilder;

    /**
     * @var ListBuilderInterface
     */
    protected $listBuilder;

    /**
     * @var QueryFactoryInterface
     */
    protected $queryFactory;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var ParametersInterface
     */
    protected $parameters;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $entityHint;

    /**
     * @var RouteGeneratorInterface
     */
    protected $routeGenerator;

    /**
     * @param DatagridBuilderInterface $datagridBuilder
     * @return void
     */
    public function setDatagridBuilder(DatagridBuilderInterface $datagridBuilder)
    {
        $this->datagridBuilder = $datagridBuilder;
    }

    /**
     * @param ListBuilderInterface $listBuilder
     * @return void
     */
    public function setListBuilder(ListBuilderInterface $listBuilder)
    {
        $this->listBuilder = $listBuilder;
    }

    /**
     * @param QueryFactoryInterface $queryManager
     * @return void
     */
    public function setQueryFactory(QueryFactoryInterface $queryManager)
    {
        $this->queryFactory = $queryManager;
    }

    /**
     * @param TranslatorInterface $translator
     * @return void
     */
    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }
    /**
     * @param ValidatorInterface $validator
     * @return void
     */
    public function setValidator(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param RouteGeneratorInterface $routeGenerator
     * @return void
     */
    public function setRouteGenerator(RouteGeneratorInterface $routeGenerator)
    {
        $this->routeGenerator = $routeGenerator;
    }

    /**
     * @param string $name
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param string $entityHint
     * @return void
     */
    public function setEntityHint($entityHint)
    {
        $this->entityHint = $entityHint;
    }

    /**
     * @param ParametersInterface $parameters
     * @return void
     */
    public function setParameters(ParametersInterface $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * @return DatagridInterface
     */
    public function getDatagrid()
    {
        // add datagrid fields
        $listCollection = $this->listBuilder->getBaseList();
        /** @var $fieldDescription FieldDescriptionInterface */
        foreach ($this->getListFields() as $fieldDescription) {
            $listCollection->add($fieldDescription);

            if ($fieldDescription->getOption('complex_data')) {
                $this->datagridBuilder->addComplexField($fieldDescription->getOption('field_name'));
            }
        }

        // create datagrid
        $datagrid = $this->datagridBuilder->getBaseDatagrid(
            $this->queryFactory->createQuery(),
            $listCollection,
            $this->routeGenerator,
            $this->parameters,
            $this->name,
            $this->entityHint
        );

        // add datagrid filters
        /** @var $fieldDescription FieldDescriptionInterface */
        foreach ($this->getFilters() as $fieldDescription) {
            $this->datagridBuilder->addFilter($datagrid, $fieldDescription);
        }

        // add datagrid sorters
        /** @var $sorterField FieldDescriptionInterface */
        foreach ($this->getSorters() as $sorterField) {
            $this->datagridBuilder->addSorter($datagrid, $sorterField);
        }

        return $datagrid;
    }

    /**
     * Get list of datagrid fields
     *
     * @abstract
     * @return FieldDescriptionInterface[]
     */
    abstract protected function getListFields();

    /**
     * Get list of datagrid filters
     *
     * @return FieldDescriptionInterface[]
     */
    protected function getFilters()
    {
        return array();
    }

    /**
     * Get list of datagrid sorters
     *
     * @return array
     */
    protected function getSorters()
    {
        return array();
    }
}
