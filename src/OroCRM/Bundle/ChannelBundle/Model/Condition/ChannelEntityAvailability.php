<?php

namespace OroCRM\Bundle\ChannelBundle\Model\Condition;

use Doctrine\Common\Collections\Collection;

use Oro\Bundle\WorkflowBundle\Exception\ConditionException;
use Oro\Bundle\WorkflowBundle\Model\Condition\ConditionInterface;
use Oro\Bundle\WorkflowBundle\Model\ContextAccessor;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;

class ChannelEntityAvailability implements ConditionInterface
{
    /** @var  Channel */
    protected $channel;

    /** @var  Array */
    protected $entities;

    /** @var  string */
    protected $message;

    /** @var  ContextAccessor */
    protected $contextAccessor;

    public function __construct(ContextAccessor $contextAccessor)
    {
        $this->contextAccessor = $contextAccessor;
    }

    /**
     * {@inheritDoc}
     */
    public function initialize(array $options)
    {
        if (2 == count($options)) {
            $this->channel = $options[0];
            $this->entities = $options[1];
        } else {
            throw new ConditionException(
                sprintf(
                    'Options must have 2 element, but %d given',
                    count($options)
                )
            );
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function isAllowed($context, Collection $errors = null)
    {
        /** @var Channel $dataChannel */
        $dataChannel = $this->contextAccessor->getValue($context, $this->channel);
        $entities = $dataChannel->getEntities();

        return count(array_intersect($this->entities, $entities)) === count($this->entities);
    }

    /**
     * {@inheritDoc}
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }
}
