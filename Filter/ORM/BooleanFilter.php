<?php

namespace Oro\Bundle\GridBundle\Filter\ORM;

use Symfony\Component\Translation\TranslatorInterface;
use Sonata\DoctrineORMAdminBundle\Filter\BooleanFilter as SonataBooleanFilter;
use Sonata\AdminBundle\Form\Type\BooleanType;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;

class BooleanFilter extends SonataBooleanFilter implements FilterInterface
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function getRenderSettings()
    {
        $renderSettings    = parent::getRenderSettings();
        $renderSettings[0] = 'oro_grid_type_filter_default';
        return $renderSettings;
    }

    /**
     * @return array
     */
    public function getValueOptions()
    {
        return array(
            BooleanType::TYPE_YES => $this->translator->trans('label_type_yes', array(), 'SonataAdminBundle'),
            BooleanType::TYPE_NO  => $this->translator->trans('label_type_no', array(), 'SonataAdminBundle')
        );
    }

    /**
     * @param ProxyQueryInterface $queryBuilder
     * @param array $value
     * @return array
     */
    protected function association(ProxyQueryInterface $queryBuilder, $value)
    {
        $alias = $this->getOption('entity_alias')
            ?: $queryBuilder->entityJoin($this->getParentAssociationMappings());

        return array($alias, $this->getFieldName());
    }
}
