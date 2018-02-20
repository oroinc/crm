<?php

namespace Oro\Bundle\SalesBundle\Handler;

use Oro\Bundle\EntityBundle\Form\EntityField\Handler\Processor\AbstractEntityApiHandler;
use Symfony\Component\PropertyAccess\PropertyAccess;

class OpportunityApiHandler extends AbstractEntityApiHandler
{
    const ENTITY_CLASS = 'Oro\Bundle\SalesBundle\Entity\Opportunity';

    /** @var PropertyAccess **/
    protected $accessor;

    public function __construct()
    {
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * {@inheritdoc}
     */
    public function afterProcess($entity)
    {
        return [
            'fields' => [
                'probability' => $this->accessor->getValue($entity, 'probability')
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getClass()
    {
        return self::ENTITY_CLASS;
    }
}
