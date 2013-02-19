<?php

namespace Oro\Bundle\GridBundle\Filter\ORM\Flexible;

use Sonata\DoctrineORMAdminBundle\Filter\Filter as AbstractORMFilter;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;

abstract class AbstractFlexibleFilter extends AbstractORMFilter
{
    /**
     * @var FlexibleManager
     */
    protected $flexibleManager;

    /**
     * {@inheritdoc}
     */
    public function initialize($name, array $options = array())
    {
        parent::initialize($name, $options);

        $flexibleManager = $this->getOption('flexible_manager');
        if (!$flexibleManager) {
            throw new \LogicException('Flexible entity filter must have flexible entity manager.');
        }

        $this->flexibleManager = $flexibleManager;
    }
}
