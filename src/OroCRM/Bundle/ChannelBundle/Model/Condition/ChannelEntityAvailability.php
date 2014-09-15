<?php

namespace OroCRM\Bundle\ChannelBundle\Model\Condition;

use Doctrine\Common\Collections\Collection;

use Oro\Bundle\WorkflowBundle\Exception\ConditionException;
use Oro\Bundle\WorkflowBundle\Model\Condition\ConditionInterface;
use Oro\Bundle\WorkflowBundle\Model\ContextAccessor;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\ChannelBundle\Provider\StateProvider;

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

    /** @var StateProvider */
    protected $stateProvider;

    public function __construct(ContextAccessor $contextAccessor, StateProvider $stateProvider)
    {
        $this->contextAccessor = $contextAccessor;
        $this->stateProvider   = $stateProvider;
    }

    /**
     * {@inheritDoc}
     */
    public function initialize(array $options)
    {
        if (2 === count($options)) {
            $this->channel  = $options[0];
            $this->entities = $options[1];
        } elseif (1 === count($options)) {
            $this->entities = $options[0];
        } else {
            throw new ConditionException(
                sprintf(
                    'Invalid options count: %d',
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
        if (null !== $this->channel) {
            /** @var Channel $dataChannel */
            $dataChannel = $this->contextAccessor->getValue($context, $this->channel);
            $entities    = $dataChannel->getEntities();

            $allowed = count(array_intersect($this->entities, $entities)) === count($this->entities);
        } else {
            $allowed = $this->stateProvider->isEntitiesEnabledInSomeChannel($this->entities);
        }

        return $allowed;
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
