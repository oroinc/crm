<?php

namespace Oro\Bundle\SalesBundle\Action;

use Oro\Bundle\SalesBundle\Entity\Manager\AccountCustomerManager;
use Oro\Component\Action\Action\AbstractAction;
use Oro\Component\Action\Exception\InvalidParameterException;
use Oro\Component\ConfigExpression\ContextAccessor;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

class FindCustomerAssociation extends AbstractAction
{
    /** @var AccountCustomerManager */
    protected $manager;

    public function __construct(ContextAccessor $contextAccessor, AccountCustomerManager $manager)
    {
        $this->manager = $manager;
        parent::__construct($contextAccessor);
    }

    /** @var array */
    protected $options = [];

    /**
     * {@inheritdoc}
     */
    public function initialize(array $options)
    {
        if (empty($options['customer'])) {
            throw new InvalidParameterException('Parameter "customer" is required');
        }

        if (empty($options['attribute'])) {
            throw new InvalidParameterException('Parameter "attribute" is required');
        }

        if (!$options['attribute'] instanceof PropertyPathInterface) {
            throw new InvalidParameterException('Parameter "attribute" must be valid property definition');
        }

        if (!$options['customer'] instanceof PropertyPathInterface) {
            throw new InvalidParameterException('Parameter "customer" must be valid property definition');
        }

        $this->options = $options;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function executeAction($context)
    {
        $target = $this->contextAccessor->getValue($context, $this->options['customer']);

        $this->contextAccessor->setValue(
            $context,
            $this->options['attribute'],
            $this->manager->getAccountCustomerByTarget($target, false)
        );
    }
}
