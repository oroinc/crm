<?php

namespace OroCRM\Bundle\SalesBundle\Handler;

use Symfony\Component\PropertyAccess\PropertyAccess;

use Oro\Bundle\EntityBundle\Form\EntityField\Handler\Processor\AbstractEntityApiHandler;

class OpportunityApiHandler extends AbstractEntityApiHandler
{
    const ENTITY_CLASS = 'OroCRM\Bundle\SalesBundle\Entity\Opportunity';

    /** @var PropertyAccess **/
    private $accessor;

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
